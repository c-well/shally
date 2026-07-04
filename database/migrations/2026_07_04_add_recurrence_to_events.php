<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Recurring events (2026-07-04, for the Crusade): describe the pattern once —
 *  per-weekday times + an end date — and the calendar unrolls every occurrence.
 *  stream_url = external broadcast (not Shalom's channel) for the tune-in CTA. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('events', function (Blueprint $t) {
            $t->date('recur_until')->nullable()->after('end_at');
            $t->json('recur_times')->nullable()->after('recur_until');   // {"0":["7:30 pm"],...} weekday=>times
            $t->string('stream_url', 500)->nullable()->after('recur_times');
        });
    }
    public function down(): void
    {
        Schema::table('events', function (Blueprint $t) { $t->dropColumn(['recur_until', 'recur_times', 'stream_url']); });
    }
};
