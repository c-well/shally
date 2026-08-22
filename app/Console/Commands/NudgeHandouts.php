<?php

namespace App\Console\Commands;

use App\Models\Handout;
use App\Services\PushService;
use Illuminate\Console\Command;

/**
 * The heartbeat that keeps "temporary" honest.
 *
 * Runs daily. Two jobs:
 *   1. Nudge every 'open' handout that has gone a full cycle without being
 *      asked about. The push goes to the clerks, because a reminder that waits
 *      inside an admin screen nobody opens is not a reminder.
 *   2. Report expiring handouts as they die, so nobody is surprised that a
 *      link they printed on a bulletin insert stopped working.
 *
 * Deliberately does NOT hard-delete anything. Expiry already kills the link
 * (HandoutController 404s on it); the row stays so the audit trail and the
 * view counts survive, and so a clerk who killed something by accident can put
 * it back with the same token.
 */
class NudgeHandouts extends Command
{
    protected $signature = 'handouts:nudge {--dry : list what would be sent, send nothing}';

    protected $description = 'Ask the clerks whether open handouts are still needed';

    public function handle(PushService $push): int
    {
        $due = Handout::whereNull('deleted_at')
            ->where('mode', 'open')
            ->get()
            ->filter(fn (Handout $h) => $h->isNudgeDue());

        if ($due->isEmpty()) {
            $this->info('Nothing due.');

            return self::SUCCESS;
        }

        foreach ($due as $h) {
            $age  = $h->ageInDays();
            $seen = $h->uniques === 1 ? '1 person' : $h->uniques . ' people';

            $line = "\"{$h->title}\" has been up {$age} days · {$seen} opened it. Still needed?";

            $this->line(($this->option('dry') ? '[dry] ' : '') . $line);

            if ($this->option('dry')) {
                continue;
            }

            $push->toClerks('Still needed?', $line, '/admin/handouts');
            $h->update(['nudged_at' => now()]);
        }

        $this->info($due->count() . ' nudge(s) ' . ($this->option('dry') ? 'would be sent' : 'sent') . '.');

        return self::SUCCESS;
    }
}
