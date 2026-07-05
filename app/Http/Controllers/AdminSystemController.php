<?php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Admin hub "system strip": what the stack runs on, and an on-demand
 * check against packagist for new releases + security advisories.
 * (Karlon 2026-07-04: versions at the bottom of admin home + a way
 * to check for new releases. Upgrades themselves stay manual —
 * docs/UPGRADE-13.md is the playbook.)
 */
class AdminSystemController extends Controller
{
    /** GET /admin/system/updates — composer outdated+audit, cached 1h. */
    public function updates(): JsonResponse
    {
        $data = Cache::remember('admin.system.updates', 3600, function () {
            $env = 'COMPOSER_HOME=/home/shalom/.config/composer COMPOSER_MEMORY_LIMIT=-1';
            $bin = '/opt/cpanel/ea-php83/root/usr/bin/php /usr/local/bin/composer';
            $cwd = base_path();

            $outdatedRaw = shell_exec("cd {$cwd} && {$env} {$bin} outdated --direct --format=json --no-interaction 2>/dev/null");
            $auditRaw    = shell_exec("cd {$cwd} && {$env} {$bin} audit --format=json --no-interaction 2>/dev/null");

            $outdated = json_decode((string) $outdatedRaw, true)['installed'] ?? [];
            $audit    = json_decode((string) $auditRaw, true);
            $advisoryCount = is_array($audit['advisories'] ?? null) ? count($audit['advisories']) : 0;

            $rows = collect($outdated)->map(fn ($p) => [
                'name'    => $p['name'],
                'current' => $p['version'],
                'latest'  => $p['latest'],
                'major'   => ($p['latest-status'] ?? '') === 'update-possible',   // major jump
            ])->values()->all();

            return [
                'checked_at' => now()->format('g:i A'),
                'advisories' => $advisoryCount,
                'outdated'   => $rows,
            ];
        });

        return response()->json(['ok' => true] + $data);
    }
}
