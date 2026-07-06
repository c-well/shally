<?php
namespace App\Console\Commands;

use App\Models\AppSetting;
use Illuminate\Console\Command;

/**
 * Nightly release/advisory check (web SAPI has shell_exec disabled — cPanel —
 * so composer runs HERE on CLI and the hub reads the stored result).
 * Result surfaces two ways: the system strip at the hub's foot (reference),
 * and a red banner at the hub's HEAD when advisories > 0 (urgency self-promotes).
 */
class SystemCheckUpdatesCommand extends Command
{
    protected $signature = 'system:check-updates';
    protected $description = 'composer outdated + audit → AppSetting for the admin hub';

    public function handle(): int
    {
        $env = 'COMPOSER_HOME=/home/shalom/.config/composer COMPOSER_MEMORY_LIMIT=-1';
        $bin = '/opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/composer';
        $cwd = base_path();

        $outdated = json_decode((string) shell_exec("cd {$cwd} && {$env} {$bin} outdated --direct --format=json --no-interaction 2>/dev/null"), true)['installed'] ?? [];
        $audit    = json_decode((string) shell_exec("cd {$cwd} && {$env} {$bin} audit --format=json --no-interaction 2>/dev/null"), true);

        $payload = [
            'checked_at' => now()->toDateTimeString(),
            'advisories' => is_array($audit['advisories'] ?? null) ? count($audit['advisories']) : 0,
            'outdated'   => collect($outdated)->map(fn ($p) => [
                'name' => $p['name'], 'current' => $p['version'], 'latest' => $p['latest'],
                'major' => ($p['latest-status'] ?? '') === 'update-possible',
            ])->values()->all(),
        ];

        AppSetting::set('system_updates_json', json_encode($payload));
        $this->info("advisories={$payload['advisories']} outdated=" . count($payload['outdated']));
        return self::SUCCESS;
    }
}
