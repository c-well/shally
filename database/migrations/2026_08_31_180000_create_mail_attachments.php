<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('mail_message_id')->constrained()->cascadeOnDelete();

            // What the sender called it — shown, never used as a path.
            $t->string('name', 255);
            // What we called it. Generated, so a sender cannot pick our paths.
            $t->string('stored_name', 64)->unique();

            $t->string('mime', 128)->nullable();
            $t->unsignedBigInteger('bytes')->default(0);

            // Where it sits in the original message, so a file we have not
            // cached (or have since evicted) can be pulled again on demand.
            // Dovecot keeps the original, so nothing here is ever the only copy.
            $t->unsignedSmallInteger('part_index')->default(0);

            // Null means "not on our disk right now" — not "missing".
            $t->timestamp('cached_at')->nullable();

            $t->timestamps();
            $t->index(['mail_message_id']);
            $t->index(['cached_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_attachments');
    }
};
