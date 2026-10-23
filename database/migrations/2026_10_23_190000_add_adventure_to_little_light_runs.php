<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('little_light_runs', fn (Blueprint $table) => $table->string('game', 24)->default('little_light')->index());
    }

    public function down(): void
    {
        Schema::table('little_light_runs', fn (Blueprint $table) => $table->dropColumn('game'));
    }
};
