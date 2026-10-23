<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('little_light_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique();
            $table->char('browser_hash', 64)->index();
            $table->unsignedTinyInteger('chapter');
            $table->boolean('is_test')->default(false)->index();
            $table->unsignedInteger('sequence')->default(0);
            $table->unsignedInteger('active_seconds')->default(0);
            $table->unsignedTinyInteger('furthest_percent')->default(0);
            $table->unsignedTinyInteger('scrolls')->default(0);
            $table->unsignedTinyInteger('lights')->default(0);
            $table->unsignedTinyInteger('sheep')->default(0);
            $table->unsignedTinyInteger('stones')->default(0);
            $table->unsignedInteger('stumbles')->default(0);
            $table->string('status', 16)->default('playing');
            $table->timestamp('last_seen_at');
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['is_test', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('little_light_runs');
    }
};
