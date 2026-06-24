<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-delete support for messages (2026-06-18).
 * Deletes become recoverable — the manual delete button and the spam
 * auto-sweep both set deleted_at rather than destroying rows. A separate
 * prune hard-deletes trash older than 30 days.
 *
 * spam_swept_at marks rows the auto-sweep removed (vs. a human delete), so
 * the admin can tell "the system cleaned this" from "I deleted this".
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $t) {
            $t->softDeletes();
            $t->timestamp('spam_swept_at')->nullable()->after('read_at');
        });
        Schema::table('prayer_requests', function (Blueprint $t) {
            $t->softDeletes();
            // prayer requests are never auto-swept; column added only for symmetry
            $t->timestamp('spam_swept_at')->nullable()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $t) {
            $t->dropSoftDeletes();
            $t->dropColumn('spam_swept_at');
        });
        Schema::table('prayer_requests', function (Blueprint $t) {
            $t->dropSoftDeletes();
            $t->dropColumn('spam_swept_at');
        });
    }
};
