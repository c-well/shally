<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_messages', function (Blueprint $t) {
            // A message that leaves the server used to be deleted outright.
            // That is fine for a page that re-reads the table on every load,
            // and wrong for a phone that has been offline for a week: the row
            // simply stops being mentioned, so the phone keeps showing it
            // forever. Keeping the row and marking it gone gives the client
            // something to learn from.
            $t->softDeletes();

            // The delta cursor. updated_at alone is not enough — several rows
            // share a second, and a client resuming mid-second would skip the
            // rest of them — so the cursor is (updated_at, id) and this index
            // matches it.
            $t->index(['updated_at', 'id'], 'mail_messages_delta_index');
        });
    }

    public function down(): void
    {
        Schema::table('mail_messages', function (Blueprint $t) {
            $t->dropIndex('mail_messages_delta_index');
            $t->dropSoftDeletes();
        });
    }
};
