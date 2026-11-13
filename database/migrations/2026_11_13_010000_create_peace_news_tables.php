<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Peace Notes News: a weekly issue drafted from Sabbath's sermon, approved by
 * Andre or KC (first approval sends), emailed Sunday morning, and answered by
 * readers ("What spoke to you?"). Answers wait for approval before showing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peace_news_issues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sermon_id')->unique()->constrained('peace_sermons')->cascadeOnDelete();
            $t->string('slug', 120)->unique();
            $t->string('status', 16)->default('draft');      // draft | approved | held | sent
            $t->json('questions');                            // 2-3 reflection questions
            $t->text('opening_summary')->nullable();
            $t->string('opening_quote', 400)->nullable();     // verbatim from the transcript
            $t->unsignedInteger('opening_seconds')->nullable();
            $t->text('closing_summary')->nullable();
            $t->string('closing_quote', 400)->nullable();
            $t->unsignedInteger('closing_seconds')->nullable();
            $t->json('checks')->nullable();                   // "check before sending" items
            $t->char('review_token', 64)->unique();
            $t->timestamp('review_sent_at')->nullable();
            $t->string('approved_by', 120)->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->timestamp('send_after');                      // Sunday 8 AM after the sermon
            $t->timestamp('sent_at')->nullable();
            $t->unsignedInteger('sent_count')->default(0);
            $t->timestamps();
            $t->index(['status', 'send_after']);
        });

        Schema::create('peace_news_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('issue_id')->constrained('peace_news_issues')->cascadeOnDelete();
            $t->foreignId('subscriber_id')->nullable()->constrained('peace_subscribers')->nullOnDelete();
            $t->string('first_name', 40)->nullable();
            $t->text('body');
            $t->boolean('share_ok')->default(true);
            $t->string('status', 16)->default('unverified');  // unverified | pending | approved | rejected
            $t->string('reviewed_by', 120)->nullable();
            $t->timestamp('reviewed_at')->nullable();
            $t->string('ip_hash', 64)->nullable();
            $t->timestamps();
            $t->index(['issue_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_news_answers');
        Schema::dropIfExists('peace_news_issues');
    }
};
