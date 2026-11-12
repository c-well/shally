<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Telling André when songs come in.
 *
 * notified_at marks a song as already reported in an evening email, so the
 * next evening only lists what is new. rundown_sent_at stops the night-before
 * rundown going out twice for the same day.
 *
 * Every song that exists today is marked as reported, so the first email
 * after this goes live does not dredge up October's music day.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music_entries', function (Blueprint $t) {
            $t->timestamp('notified_at')->nullable()->after('ip_hash');
        });
        Schema::table('music_days', function (Blueprint $t) {
            $t->timestamp('rundown_sent_at')->nullable()->after('files_purged_at');
        });

        DB::table('music_entries')->update(['notified_at' => now()]);
        DB::table('music_days')->where('service_date', '<', now('America/New_York')->toDateString())
            ->update(['rundown_sent_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('music_entries', fn (Blueprint $t) => $t->dropColumn('notified_at'));
        Schema::table('music_days', fn (Blueprint $t) => $t->dropColumn('rundown_sent_at'));
    }
};
