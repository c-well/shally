<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── peace_subscribers ──────────────────────────────────────────
        // A "seeker" account. Email is the account; no password.
        // Created lazily the first time someone hits the magic-link form.
        Schema::create('peace_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable()
                ->comment('Set on first successful magic-link click.');
            $table->string('source', 60)->nullable()
                ->comment('Where the signup originated — save_qa | newsletter | baptism | submit_question | direct');
            $table->boolean('subscribed_newsletter')->default(false)
                ->comment('Opt-in for weekly Q&A email. Defaults OFF.');
            $table->string('ip_hash', 64)->nullable();
            $table->string('ua_hash', 64)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index('email_verified_at');
            $table->index('last_seen_at');
        });

        // ── peace_subscriber_magic_links ───────────────────────────────
        // Short-lived sha256-hashed tokens for passwordless sign-in.
        // 30-min expiry, single-use, optional "intent" payload so the
        // verify action can perform a Save-Q&A or similar on consume.
        Schema::create('peace_subscriber_magic_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('peace_subscribers')->cascadeOnDelete();
            $table->string('token_hash', 64)->unique()
                ->comment('sha256 of the raw token. Raw token is only in the emailed URL.');
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable();
            $table->json('intent')->nullable()
                ->comment('Optional action to perform on consume: {"type":"save_qa","qa_id":N} etc.');
            $table->string('source_url', 500)->nullable()
                ->comment('Where to redirect after verify.');
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['subscriber_id', 'used_at']);
        });

        // ── peace_saved_qas ────────────────────────────────────────────
        // A subscriber's bookmarked Q&As. Visible at /find-peace/saved.
        Schema::create('peace_saved_qas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('peace_subscribers')->cascadeOnDelete();
            $table->foreignId('qa_id')->constrained('peace_qa_pairs')->cascadeOnDelete();
            $table->timestamp('saved_at')->useCurrent();
            $table->unique(['subscriber_id', 'qa_id']);
            $table->index('saved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_saved_qas');
        Schema::dropIfExists('peace_subscriber_magic_links');
        Schema::dropIfExists('peace_subscribers');
    }
};
