<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $table->boolean('always_show_during_week')->default(false)->after('event_ends_at');
        });
    }
    public function down(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $table->dropColumn('always_show_during_week');
        });
    }
};
