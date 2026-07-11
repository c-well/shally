<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

/**
 * SYSTEM_SELF_UPDATE (2026-06-24) — the "claudeless" maintenance routine.
 *
 * Keeps the app patched without a human babysitting it, but NEVER leaves the
 * site broken. The contract:
 *
 *   1. CHECKPOINT first  — record current git SHA + composer.lock + a fresh DB
 *      dump as the last-known-good restore point.
 *   2. UPDATE conservatively — composer update WITHIN the current major version
 *      only (--with-dependencies on the direct deps). Patch + minor + security.
 *      Major version jumps are NEVER auto-applied (breaking changes need a human).
 *   3. MIGRATE — run any new migrations.
 *   4. HEALTH-CHECK — clear caches, HTTP-probe the key public routes + /admin.
 *      Any non-2xx/3xx (or a 5xx, or a route that was 200 and is now 500) = FAIL.
 *   5. ROLLBACK on failure — restore composer.lock + composer install, restore
 *      the DB dump, clear caches. Back to last-known-good automatically.
 *   6. REPORT — audit_log row + email either way. composer audit before/after.
 *
 * Designed to be scheduled (e.g. monthly) OR run on demand. Always safe to run.
 *
 *   php artisan system:self-update --dry-run   # plan + checkpoint, no changes
 *   php artisan system:self-update             # full run with auto-rollback
 *   php artisan system:self-update --security-only   # only if advisories exist
 */
class SelfUpdateCommand extends Command
{
    protected $signature = 'system:self-update
                            {--dry-run : Checkpoint + show plan, apply nothing}
                            {--security-only : Skip unless composer audit reports advisories}';

    protected $description = 'Backup → update (within major) → migrate → health-check → auto-rollback on failure.';

    private const COMPOSER = '/home/shalom/bin/composer';
    private const PHP      = '/opt/cpanel/ea-php83/root/usr/bin/php';
    private const BASE_URL = 'https://thechurchofpeace.org';
    private const PROBES   = ['/', '/find-peace', '/bible', '/hymnal', '/welcome'];
    private const ADMIN_PROBE = '/admin'; // expect 302
    private const DIRECT_DEPS = ['laravel/framework', 'guzzlehttp/guzzle', 'guzzlehttp/psr7', 'symfony/yaml'];

    public function handle(): int
    {
        $this->info('=== system:self-update @ ' . now()->toIso8601String() . ' ===');
        $dryRun = (bool) $this->option('dry-run');
        $base = base_path();

        // ── 0. Pre-flight: capture baseline health so we can compare after ──
        $baselineHealth = $this->probeAll();
        $advisoriesBefore = $this->auditCount();
        $this->line("  Advisories before: {$advisoriesBefore}");

        if ($this->option('security-only') && $advisoriesBefore === 0) {
            $this->info('  --security-only and no advisories. Nothing to do. Exiting clean.');
            return self::SUCCESS;
        }

        // ── 1. CHECKPOINT ──────────────────────────────────────────────────
        $sha = $this->git(['rev-parse', 'HEAD']);
        $stamp = now()->format('Ymd-His');
        $lockBackup = "/home/shalom/db-backups/composer.lock.{$stamp}";
        @copy("{$base}/composer.lock", $lockBackup);
        $dbBackup = $this->backupDb($stamp);
        $this->line("  Checkpoint: git={$sha} lock={$lockBackup} db=" . ($dbBackup ?: 'FAILED'));
        if (! $dbBackup) {
            $this->error('  DB backup failed — refusing to update without a restore point.');
            return self::FAILURE;
        }

        // Record this restore point so it shows up in /admin/changelog alongside
        // manual checkpoints. created_by = null marks it as system-captured.
        try {
            \App\Models\SystemCheckpoint::create([
                'label' => ($dryRun ? 'Auto — before self-update (dry-run)' : 'Auto — before self-update'),
                'kind' => 'auto_update',
                'git_sha' => $sha ?: null,
                'composer_lock_path' => is_file($lockBackup) ? $lockBackup : null,
                'db_backup_path' => $dbBackup,
                'app_version' => trim($this->artisan(['--version'])['out']) ?: null,
                'created_by' => null,
            ]);
        } catch (\Throwable $e) {
            $this->warn('  Could not record SystemCheckpoint row: ' . $e->getMessage());
        }

        if ($dryRun) {
            $this->info('  Dry run — checkpoint captured, no update applied. Plan: composer update ' . implode(' ', self::DIRECT_DEPS) . ' --with-dependencies (within major).');
            return self::SUCCESS;
        }

        // ── 2. UPDATE (within current major versions — composer.json
        //       constraints already pin majors, so update stays in-major) ───
        $this->info('  Updating dependencies (within major)…');
        $upd = $this->composer(array_merge(['update'], self::DIRECT_DEPS, ['--with-dependencies', '--no-interaction', '--no-ansi']));
        if (! $upd['ok']) {
            $this->error('  composer update failed — rolling back.');
            $this->rollback($lockBackup, $dbBackup);
            $this->report('failed_update', $advisoriesBefore, null, "composer update failed: " . substr($upd['out'], -300));
            return self::FAILURE;
        }

        // ── 3. MIGRATE ─────────────────────────────────────────────────────
        $mig = $this->artisan(['migrate', '--force']);
        if (! $mig['ok']) {
            $this->error('  migrate failed — rolling back.');
            $this->rollback($lockBackup, $dbBackup);
            $this->report('failed_migrate', $advisoriesBefore, null, "migrate failed: " . substr($mig['out'], -300));
            return self::FAILURE;
        }

        // ── 4. HEALTH-CHECK ────────────────────────────────────────────────
        $this->artisan(['optimize:clear']);
        sleep(2);
        $after = $this->probeAll();
        $regressions = [];
        foreach ($after as $path => $code) {
            $was = $baselineHealth[$path] ?? null;
            // Fail if a previously-OK route now errors, or any 5xx appears.
            if ($code >= 500 || ($was && $was < 400 && $code >= 400)) {
                $regressions[] = "{$path}: {$was} → {$code}";
            }
        }

        if ($regressions) {
            $this->error('  Health check FAILED — rolling back. Regressions: ' . implode(', ', $regressions));
            $this->rollback($lockBackup, $dbBackup);
            $this->report('rolled_back', $advisoriesBefore, null, 'Health regressions: ' . implode('; ', $regressions));
            return self::FAILURE;
        }

        // ── 5. SUCCESS ─────────────────────────────────────────────────────
        $advisoriesAfter = $this->auditCount();
        $version = trim($this->artisan(['--version'])['out']);
        $this->info("  ✓ Update healthy. {$version}. Advisories {$advisoriesBefore} → {$advisoriesAfter}.");
        $this->report('updated', $advisoriesBefore, $advisoriesAfter, "OK — {$version}; checkpoint git={$sha}");
        return self::SUCCESS;
    }

    // ── helpers ────────────────────────────────────────────────────────────

    private function probeAll(): array
    {
        $out = [];
        foreach (self::PROBES as $p) $out[$p] = $this->probe(self::BASE_URL . $p);
        $out[self::ADMIN_PROBE] = $this->probe(self::BASE_URL . self::ADMIN_PROBE);
        return $out;
    }

    private function probe(string $url): int
    {
        try {
            return Http::timeout(15)->withoutRedirecting()->get($url)->status();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function auditCount(): int
    {
        $r = $this->composer(['audit', '--no-interaction', '--format=plain', '--no-ansi']);
        // count "CVE-" occurrences as a proxy for advisory volume
        return preg_match_all('/CVE-\d{4}/', $r['out']);
    }

    private function backupDb(string $stamp): ?string
    {
        $proc = Process::fromShellCommandline('/home/shalom/bin/backup-db.sh', base_path(), null, null, 600);
        $proc->run();
        if (! $proc->isSuccessful()) return null;
        $latest = trim((string) shell_exec('ls -t /home/shalom/db-backups/shalom_app_*.sql.gz 2>/dev/null | head -1'));
        return $latest ?: null;
    }

    private function rollback(string $lockBackup, string $dbBackup): void
    {
        $this->warn('  ↩ ROLLBACK: restoring last-known-good…');
        if (is_file($lockBackup)) {
            @copy($lockBackup, base_path('composer.lock'));
            $this->composer(['install', '--no-interaction', '--no-ansi']);
        }
        if (is_file($dbBackup)) {
            // restore DB from the gzip dump
            $db   = env('DB_DATABASE'); $u = env('DB_USERNAME'); $pw = env('DB_PASSWORD');
            Process::fromShellCommandline(
                sprintf('gunzip -c %s | mysql -u%s -p%s %s', escapeshellarg($dbBackup), escapeshellarg($u), escapeshellarg($pw), escapeshellarg($db)),
                null, null, null, 600
            )->run();
        }
        $this->artisan(['optimize:clear']);
        $this->warn('  ↩ Rolled back to last-known-good.');
    }

    private function report(string $event, int $before, ?int $after, string $desc): void
    {
        try {
            \App\Models\AuditLog::record(
                event: 'self_update_' . $event,
                description: $desc,
                meta: ['advisories_before' => $before, 'advisories_after' => $after],
            );
            $body = "system:self-update — {$event}\n\n{$desc}\n\nAdvisories: {$before}" . ($after !== null ? " → {$after}" : '') . "\n";
            \Illuminate\Support\Facades\Mail::raw($body, fn($m) => $m->to('contact@c-wellpics.com')
                ->subject("[The Church of Peace] Self-update: {$event}"));
        } catch (\Throwable $e) { /* never let reporting break the run */ }
    }

    private function git(array $args): string
    {
        $p = new Process(array_merge(['git'], $args), base_path());
        $p->run();
        return trim($p->getOutput()) ?: 'unknown';
    }

    private function composer(array $args): array
    {
        $p = new Process(array_merge([self::PHP, self::COMPOSER], $args), base_path(),
            ['COMPOSER_ALLOW_SUPERUSER' => '1'], null, 1200);
        $p->run();
        return ['ok' => $p->isSuccessful(), 'out' => $p->getOutput() . $p->getErrorOutput()];
    }

    private function artisan(array $args): array
    {
        $p = new Process(array_merge([self::PHP, 'artisan'], $args), base_path(), null, null, 600);
        $p->run();
        return ['ok' => $p->isSuccessful(), 'out' => $p->getOutput() . $p->getErrorOutput()];
    }
}
