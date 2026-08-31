<?php

/*
 |----------------------------------------------------------------------
 | The mail room
 |----------------------------------------------------------------------
 | Every mailbox Andre can open, in the order they appear in the picker.
 | `note` is the one line under the name — say what belongs in the box,
 | not what the box is.
 |
 | Mail is read on the scheduler with doveadm (CLI only: the FPM pools
 | have exec disabled, and that is deliberate). The room reads the
 | database and never shells out.
 */

return [

    'domain' => 'thechurchofpeace.org',

    'boxes' => [
        'media' => ['note' => 'Photos, bulletins, press'],
        'hello' => ['note' => 'Visitors and general enquiries'],
        'prayer' => ['note' => 'Prayer requests'],
        'treasurer' => ['note' => 'Receipts, invoices, giving'],
        'app' => ['note' => 'Automated site notices'],
    ],

    // Dovecot's own names. Trash sits behind the inbox, not beside it.
    'folders' => [
        'INBOX' => 'Inbox',
        'Archive' => 'Archive',
        'Sent' => 'Sent',
        'Trash' => 'Trash',
    ],

    'doveadm' => env('MAILROOM_DOVEADM', '/usr/bin/doveadm'),

    // A body longer than this is stored truncated. Nobody reads 400KB of
    // quoted reply chain, and the column should not carry it.
    'max_body' => 200000,

    'sync_limit' => 400,
];
