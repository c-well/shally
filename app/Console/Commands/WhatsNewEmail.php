<?php
namespace App\Console\Commands;

use App\Support\WhatsNew;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Email someone the What's new list, designed, with a plain-text copy.
 *
 * --preview=path writes the HTML to a file instead of sending, so it can be
 * looked at before anyone receives it. Nothing goes to André without Karlon
 * seeing it first.
 */
class WhatsNewEmail extends Command
{
    protected $signature = 'whatsnew:email {to?} {--cc=} {--first=André} {--preview=}';
    protected $description = "Email the What's new list, or write a preview";

    public function handle(): int
    {
        $data = [
            'items'     => WhatsNew::items(),
            'first'     => $this->option('first'),
            'page'      => route('admin.whats-new'),
            'preheader' => 'Music day links, an 8pm email when songs come in, and more.',
            'intro'     => 'Tap any one to see how it works.',
            'signoff'   => 'Karlon',
        ];

        if ($path = $this->option('preview')) {
            file_put_contents($path, view('emails.whats-new', $data)->render());
            $this->line("preview written to {$path}");
            return self::SUCCESS;
        }

        $to = $this->argument('to');
        if (! $to) {
            $this->error('Give an address, or --preview=path.');
            return self::FAILURE;
        }

        Mail::send(['html' => 'emails.whats-new', 'text' => 'emails.whats-new-text'], $data,
            function ($m) use ($to) {
                $m->to($to)->subject('New on the church site: Music day');
                if ($cc = $this->option('cc')) $m->cc($cc);
            });
        $this->line("sent to {$to}");
        return self::SUCCESS;
    }
}
