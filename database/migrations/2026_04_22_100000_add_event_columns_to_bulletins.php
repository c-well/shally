<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $t) {
            $t->enum('kind', ['sabbath', 'event_night'])
                ->default('sabbath')
                ->after('service_date');
            $t->enum('service_time', ['morning', 'evening'])
                ->default('morning')
                ->after('kind');
            $t->string('event_name', 120)->nullable()->after('service_time');
            $t->unsignedSmallInteger('event_night_number')->nullable()->after('event_name');
            $t->unsignedSmallInteger('event_total_nights')->nullable()->after('event_night_number');
            $t->date('event_ends_at')->nullable()->after('event_total_nights');
        });

        // Unique per service date+time. Existing rows default to 'morning' — safe if no
        // two existing bulletins share the same service_date. Confirmed no conflicts at migration time.
        Schema::table('bulletins', function (Blueprint $t) {
            $t->unique(['service_date', 'service_time'], 'bulletins_date_time_unique');
        });

        // Helper index for "find the active event night for today" queries
        DB::statement('CREATE INDEX bulletins_event_window_idx ON bulletins (kind, event_name, service_date)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX bulletins_event_window_idx ON bulletins');
        Schema::table('bulletins', function (Blueprint $t) {
            $t->dropUnique('bulletins_date_time_unique');
            $t->dropColumn([
                'kind', 'service_time', 'event_name',
                'event_night_number', 'event_total_nights', 'event_ends_at',
            ]);
        });
    }
};
