<?php

/*
 |----------------------------------------------------------------------
 | The mail room
 |----------------------------------------------------------------------
 | Every mailbox Andre can open, in the order they appear in the picker.
 | The key is the short name shown in the room; `addr` is the real
 | address, because the church has mail on two domains — the current
 | thechurchofpeace.org and the older gotoshalom.com, which still
 | receives.
 |
 | `note` is the one line under the name — say what belongs in the box,
 | not what the box is.
 |
 | Mail is read on the scheduler with doveadm (CLI only: the FPM pools
 | have exec disabled, and that is deliberate). The room reads the
 | database and never shells out.
 */

return [

    'boxes' => [
        'media' => ['addr' => 'media@thechurchofpeace.org',     'note' => 'Photos, bulletins, press'],
        'hello' => ['addr' => 'hello@thechurchofpeace.org',     'note' => 'Visitors and general enquiries'],
        'prayer' => ['addr' => 'prayer@thechurchofpeace.org',    'note' => 'Prayer requests'],
        'treasurer' => ['addr' => 'treasurer@thechurchofpeace.org', 'note' => 'Receipts, invoices, giving'],
        'app' => ['addr' => 'app@thechurchofpeace.org',       'note' => 'Automated site notices'],

        // The old domain. It still receives, and most of the history is
        // here — so it belongs in the room rather than in a webmail tab
        // nobody opens. karlon@gotoshalom.com is deliberately absent:
        // it is personal mail, not the church's.
        'media-old' => ['addr' => 'media@gotoshalom.com',     'note' => 'Media, on the old domain'],
        'treasurer-old' => ['addr' => 'treasurer@gotoshalom.com', 'note' => 'Treasurer, on the old domain'],
        'contact-old' => ['addr' => 'contact@gotoshalom.com',   'note' => 'The old contact form'],
    ],

    // Dovecot's own names, which on this box are namespaced under INBOX.
    // These strings are passed to doveadm verbatim — a pretty name here means
    // a folder that does not exist. Trash sits behind the inbox, not beside it.
    'folders' => [
        'INBOX' => 'Inbox',
        'INBOX.Archive' => 'Archive',
        'INBOX.Sent' => 'Sent',
        'INBOX.Trash' => 'Trash',
    ],

    // Where the reading pane's buttons send a message.
    'archive_to' => 'INBOX.Archive',
    'trash_to' => 'INBOX.Trash',

    'doveadm' => env('MAILROOM_DOVEADM', '/usr/bin/doveadm'),

    // A body longer than this is stored truncated. Nobody reads 400KB of
    // quoted reply chain, and the column should not carry it.
    'max_body' => 200000,

    /*
     | Attachment cache
     |
     | What we keep on our own disk is a COPY. The original stays in the
     | mailbox, so every other mail app keeps working and nothing has to be
     | tied back to anything. That makes our copy disposable: past the budget
     | we simply stop caching, and opening an uncached file pulls it from the
     | original message on the next scheduler pass.
     |
     | The account quota is the real constraint here, not the disk.
     */
    'cache_budget' => 250 * 1024 * 1024,   // per mailbox
    'cache_file_ceiling' => 25 * 1024 * 1024,    // one file

    'sync_limit' => 400,
];
