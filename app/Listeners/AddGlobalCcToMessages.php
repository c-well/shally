<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

class AddGlobalCcToMessages
{
    public function handle(MessageSending $event): void
    {
        $cc = config('mail.global_cc');
        if (! $cc) {
            return;
        }
        $message = $event->message;
        $existing = collect($message->getCc() ?? [])->map(fn($a) => strtolower($a->getAddress()))->all();
        if (! in_array(strtolower($cc), $existing, true)) {
            $message->addCc($cc);
        }
    }
}
