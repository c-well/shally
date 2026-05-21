<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('peace_sermons', function (Blueprint $table) {
            // Opaque random token used in signed approve/discard URLs (no login needed).
            // 64-char hex (sha256-derived) — same token across the whole review lifecycle.
            $table->string('review_token', 64)->nullable()->unique()->after('processing_status');
            // Deadline timestamps — driven by peace:review-tick hourly cron.
            $table->timestamp('review_deadline')->nullable()
                ->comment('When pending_review auto-drops to draft (72h after creation)')
                ->after('review_token');
            $table->timestamp('discarded_at')->nullable()
                ->comment('When clerk hit Discard — 48h later, 2nd confirm email fires')
                ->after('review_deadline');
            $table->timestamp('confirm_delete_email_sent_at')->nullable()
                ->comment('Sentinel so peace:review-tick doesn\'t spam the 48h confirm email')
                ->after('discarded_at');
            $table->timestamp('draft_email_sent_at')->nullable()
                ->comment('Sentinel so the 72h "pulled offline" email only fires once')
                ->after('confirm_delete_email_sent_at');
            $table->index('processing_status');
            $table->index('review_deadline');
            $table->index('discarded_at');
        });
    }

    public function down(): void
    {
        Schema::table('peace_sermons', function (Blueprint $table) {
            $table->dropIndex(['processing_status']);
            $table->dropIndex(['review_deadline']);
            $table->dropIndex(['discarded_at']);
            $table->dropColumn([
                'review_token',
                'review_deadline',
                'discarded_at',
                'confirm_delete_email_sent_at',
                'draft_email_sent_at',
            ]);
        });
    }
};
