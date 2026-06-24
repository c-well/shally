<?php
namespace App\Http\Controllers;

use App\Models\SystemCheckpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;

/**
 * /admin/changelog — the update log + restore-point control panel.
 *
 * Shows the human CHANGELOG.md, recent git commits, and the captured
 * checkpoints (last-known-good restore points). Authorized users can roll back
 * to a checkpoint: it captures a fresh pre-rollback checkpoint first (so a
 * rollback is itself undoable), then restores composer.lock + DB and clears
 * caches. Gated to super_admins + anyone with can_rollback.
 */
class AdminChangelogController extends Controller
{
    private const PHP      = '/opt/cpanel/ea-php83/root/usr/bin/php';
    private const COMPOSER = '/home/shalom/bin/composer';

    public function index(): View
    {
        $html = '';
        $path = base_path('docs/CHANGELOG.md');
        if (is_file($path)) {
            $html = \Illuminate\Support\Str::markdown(file_get_contents($path));
        }

        $checkpoints = SystemCheckpoint::orderByDesc('created_at')->limit(50)->get();

        // Recent git commits (read-only, for context)
        $commits = [];
        try {
            $p = Process::path(base_path())->run(['git', 'log', '-15', '--pretty=format:%h%x09%ad%x09%s', '--date=short']);
            if ($p->successful()) {
                foreach (explode("\n", trim($p->output())) as $line) {
                    [$sha, $date, $subj] = array_pad(explode("\t", $line, 3), 3, '');
                    if ($sha) $commits[] = compact('sha', 'date', 'subj');
                }
            }
        } catch (\Throwable $e) { /* git context is best-effort */ }

        return view('admin.changelog', [
            'html'        => $html,
            'checkpoints' => $checkpoints,
            'commits'     => $commits,
            'canRollback' => $this->userCanRollback(request()),
        ]);
    }

    /** POST /admin/changelog/checkpoint — capture a manual restore point. */
    public function checkpoint(Request $request): RedirectResponse|JsonResponse
    {
        if (! $this->userCanRollback($request)) abort(403);

        $label = trim((string) $request->input('label')) ?: 'Manual checkpoint';
        $cp = $this->capture($label, 'manual', $request->user()?->id);

        $msg = $cp ? 'Checkpoint captured.' : 'Checkpoint failed (DB backup error).';
        if ($request->wantsJson()) return response()->json(['ok' => (bool) $cp, 'message' => $msg]);
        return back()->with('status', $msg);
    }

    /** POST /admin/changelog/{checkpoint}/restore — roll back to a checkpoint. */
    public function restore(Request $request, int $id): RedirectResponse|JsonResponse
    {
        if (! $this->userCanRollback($request)) abort(403);

        $cp = SystemCheckpoint::findOrFail($id);
        if (! $cp->isRestorable()) {
            $m = 'This checkpoint can no longer be restored — its backup files are gone.';
            return $request->wantsJson() ? response()->json(['ok' => false, 'message' => $m], 422) : back()->with('status', $m);
        }

        // Safety net: capture the CURRENT state first, so this rollback is undoable.
        $this->capture('Auto — before rollback to #' . $cp->id, 'pre_rollback', $request->user()?->id);

        $errors = [];

        // 1. Restore composer.lock + install (dependency state)
        if ($cp->composer_lock_path && is_file($cp->composer_lock_path)) {
            @copy($cp->composer_lock_path, base_path('composer.lock'));
            $p = Process::path(base_path())->timeout(1200)
                ->env(['COMPOSER_ALLOW_SUPERUSER' => '1'])
                ->run([self::PHP, self::COMPOSER, 'install', '--no-interaction', '--no-ansi']);
            if (! $p->successful()) $errors[] = 'composer install failed';
        }

        // 2. Restore DB from the dump
        if ($cp->db_backup_path && is_file($cp->db_backup_path)) {
            $db = env('DB_DATABASE'); $u = env('DB_USERNAME'); $pw = env('DB_PASSWORD');
            $cmd = sprintf('gunzip -c %s | mysql -u%s -p%s %s',
                escapeshellarg($cp->db_backup_path), escapeshellarg($u), escapeshellarg($pw), escapeshellarg($db));
            $p = Process::timeout(1200)->run($cmd);
            if (! $p->successful()) $errors[] = 'DB restore failed';
        }

        // 3. Clear caches
        Process::path(base_path())->run([self::PHP, 'artisan', 'optimize:clear']);

        $cp->forceFill(['restored_at' => now(), 'restored_by' => $request->user()?->id])->save();

        \App\Models\AuditLog::record(
            event: $errors ? 'system_rollback_partial' : 'system_rollback',
            userId: $request->user()?->id,
            description: ($errors ? 'Rollback to checkpoint #'.$cp->id.' with errors: '.implode('; ', $errors)
                                  : 'Rolled back to checkpoint #'.$cp->id.' ('.$cp->label.')'),
            meta: ['checkpoint_id' => $cp->id, 'errors' => $errors],
        );

        $msg = $errors
            ? 'Rollback completed with issues: ' . implode('; ', $errors) . '. Check the site.'
            : 'Rolled back to "' . $cp->label . '". Caches cleared.';
        return $request->wantsJson() ? response()->json(['ok' => empty($errors), 'message' => $msg]) : back()->with('status', $msg);
    }

    /** Capture a checkpoint: git SHA + composer.lock copy + DB dump. */
    private function capture(string $label, string $kind, ?int $userId): ?SystemCheckpoint
    {
        $stamp = now()->format('Ymd-His');
        $base = base_path();

        // git sha
        $sha = null;
        try {
            $p = Process::path($base)->run(['git', 'rev-parse', 'HEAD']);
            if ($p->successful()) $sha = trim($p->output());
        } catch (\Throwable $e) {}

        // composer.lock copy
        $lockDest = "/home/shalom/db-backups/composer.lock.{$stamp}";
        @copy("{$base}/composer.lock", $lockDest);
        if (! is_file($lockDest)) $lockDest = null;

        // DB dump
        $dbDest = null;
        try {
            $p = Process::timeout(600)->run('/home/shalom/bin/backup-db.sh');
            if ($p->successful()) {
                $latest = trim((string) shell_exec('ls -t /home/shalom/db-backups/shalom_app_*.sql.gz 2>/dev/null | head -1'));
                $dbDest = $latest ?: null;
            }
        } catch (\Throwable $e) {}

        if (! $dbDest) return null; // no restore point without a DB dump

        $version = null;
        try { $version = trim(Process::path($base)->run([self::PHP, 'artisan', '--version'])->output()); } catch (\Throwable $e) {}

        return SystemCheckpoint::create([
            'label' => $label, 'kind' => $kind, 'git_sha' => $sha,
            'composer_lock_path' => $lockDest, 'db_backup_path' => $dbDest,
            'app_version' => $version, 'created_by' => $userId,
        ]);
    }

    private function userCanRollback(Request $request): bool
    {
        $u = auth()->user() ?? $request->user();
        return $u && ($u->role === 'super_admin' || (bool) ($u->can_rollback ?? false));
    }
}
