<?php

namespace App\Console\Commands;

use App\Models\MailMessage;
use Illuminate\Console\Command;

/**
 * Tombstones exist so a client that was offline can find out what went away.
 * They are not a record — after long enough, a client that stale has to start
 * over anyway, and the delta endpoint tells it so. So they are swept.
 */
class MailPruneTombstones extends Command
{
    protected $signature = 'mail:prune-tombstones {--days=90}';

    protected $description = 'Forget messages that were deleted long enough ago that no client is still catching up';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $gone = MailMessage::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays($days))
            ->forceDelete();

        if ($gone) {
            $this->info("swept {$gone} tombstone".($gone === 1 ? '' : 's')." older than {$days} days");
        }

        return self::SUCCESS;
    }
}
