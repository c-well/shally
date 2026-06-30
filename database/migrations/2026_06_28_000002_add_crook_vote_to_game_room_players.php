<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Undercover: each player's final accusation (which codename is the Crook). */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('game_room_players', function (Blueprint $t) {
            $t->string('crook_vote', 32)->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('game_room_players', function (Blueprint $t) {
            $t->dropColumn('crook_vote');
        });
    }
};
