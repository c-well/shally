<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_players', function (Blueprint $t) {
            $t->string('review_status', 16)->default('auto');
            $t->unsignedBigInteger('reviewed_by')->nullable();
            $t->timestamp('reviewed_at')->nullable();
        });
        Schema::table('game_progress', function (Blueprint $t) {
            $t->unsignedTinyInteger('earned_stars')->default(0);
        });
        Schema::create('game_runs', function (Blueprint $t) {
            $t->id();
            $t->uuid('token')->unique();
            $t->unsignedBigInteger('game_player_id')->index();
            $t->unsignedBigInteger('game_level_id')->index();
            $t->string('game_type', 24);
            $t->string('verse_hash', 64);
            $t->json('challenge');
            $t->json('state');
            $t->unsignedInteger('sequence')->default(0);
            $t->string('last_event_hash', 64)->nullable();
            $t->unsignedInteger('actions')->default(0);
            $t->unsignedInteger('active_seconds')->default(0);
            $t->unsignedInteger('invalid_actions')->default(0);
            $t->boolean('qualifies')->default(false);
            $t->timestamp('last_action_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['game_player_id', 'qualifies']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_runs');
        Schema::table('game_progress', fn (Blueprint $t) => $t->dropColumn('earned_stars'));
        Schema::table('game_players', fn (Blueprint $t) => $t->dropColumn(['review_status', 'reviewed_by', 'reviewed_at']));
    }
};
