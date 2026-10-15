<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sending, added to the mail room.
 *
 * The room has been read-and-triage since it was built. Everything it does to
 * a mailbox already goes through mail_actions — the web process writes an
 * intent, the scheduler makes it true a minute later, and the web never
 * touches the mail store. Sending joins that queue rather than going around
 * it, for the same reason: the web SAPI has exec disabled by php_admin_value
 * on every FPM pool, so it cannot run doveadm to file the sent copy even if
 * it wanted to.
 *
 * mail_actions gains a payload because a send carries a body; the other seven
 * actions are just a verb against a UID and need nothing.
 *
 * mail_messages gains the two headers a reply needs to land in the right
 * thread. Existing rows have neither — mail:sync fills them going forward,
 * and the sender falls back to from_email and message_id, which threads
 * correctly in every client that matters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_actions', function (Blueprint $t) {
            // { to, cc, subject, body, in_reply_to, references, from_addr }
            $t->json('payload')->nullable()->after('action');
        });

        Schema::table('mail_messages', function (Blueprint $t) {
            // Where the sender asked to be written back to, when that is not
            // the From address. A list that posts as "noreply@" but sets
            // Reply-To is common enough to be worth honouring.
            $t->string('reply_to', 191)->nullable()->after('from_email');
            // The References chain, so a reply continues the thread rather
            // than starting a sibling next to it.
            $t->text('refs')->nullable()->after('message_id');
        });
    }

    public function down(): void
    {
        Schema::table('mail_actions', fn (Blueprint $t) => $t->dropColumn('payload'));
        Schema::table('mail_messages', fn (Blueprint $t) => $t->dropColumn(['reply_to', 'refs']));
    }
};
