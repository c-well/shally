<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mail lives in mdbox on this box and the web process cannot shell out
 * (disable_functions on the FPM pool), so doveadm runs on the scheduler and
 * lands messages here. The admin room then reads the database, which is why
 * it opens instantly and can search all five mailboxes at once.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_messages', function (Blueprint $table) {
            $table->id();

            // Which mailbox this came from — 'media', 'hello', 'prayer', ...
            $table->string('mailbox', 32)->index();
            // Dovecot's UID, unique per mailbox. Together they identify a message.
            $table->unsignedBigInteger('uid');
            $table->string('folder', 64)->default('INBOX');

            $table->string('message_id', 255)->nullable()->index();
            $table->string('from_name', 191)->nullable();
            $table->string('from_email', 191)->nullable()->index();
            $table->string('subject', 512)->nullable();
            $table->text('preview')->nullable();      // first ~300 chars, for the list
            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();

            $table->timestamp('sent_at')->nullable()->index();
            $table->boolean('seen')->default(false)->index();
            $table->boolean('flagged')->default(false);
            $table->boolean('has_attachments')->default(false);

            // What kind of mail this is, decided once at ingest.
            //   person   — a human wrote to us and expects an answer
            //   update   — newsletters, notifications, automated reports
            //   receipt  — orders, invoices, payment confirmations
            //   unknown  — not classified yet
            $table->string('kind', 16)->default('unknown')->index();
            $table->unsignedTinyInteger('kind_confidence')->nullable();
            $table->string('kind_reason', 255)->nullable();

            $table->timestamps();

            $table->unique(['mailbox', 'folder', 'uid']);
            $table->index(['mailbox', 'sent_at']);
        });

        // Actions taken in the browser land here and the scheduler applies them
        // with doveadm on its next tick. The UI updates immediately either way,
        // so this lag is invisible in normal use.
        Schema::create('mail_actions', function (Blueprint $table) {
            $table->id();
            $table->string('mailbox', 32);
            $table->string('folder', 64)->default('INBOX');
            $table->unsignedBigInteger('uid');
            $table->string('action', 24);                 // seen | unseen | archive | trash | flag | unflag
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->timestamp('applied_at')->nullable()->index();
            $table->string('error', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_actions');
        Schema::dropIfExists('mail_messages');
    }
};
