<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('peace_sermons', function (Blueprint $table) {
            $table->boolean('is_offsite')->default(false)
                  ->after('boundary_source')
                  ->comment('Sermon was streamed from an offsite location (camp meeting, conference, visiting venue) — NOT published to /find-peace');
            $table->boolean('is_no_sermon')->default(false)
                  ->after('is_offsite')
                  ->comment('Stream contained no sermon (prayer service, special program) — kept in DB but never published');
            $table->index(['is_offsite', 'is_no_sermon']);
        });
    }

    public function down(): void
    {
        Schema::table('peace_sermons', function (Blueprint $table) {
            $table->dropIndex(['is_offsite', 'is_no_sermon']);
            $table->dropColumn(['is_offsite', 'is_no_sermon']);
        });
    }
};
