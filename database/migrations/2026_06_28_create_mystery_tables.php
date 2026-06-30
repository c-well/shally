<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Teen hidden-identity mystery game (2026-06-28) — working title "Undercover".
 *
 * Everyone joins anonymously; the app hides each player behind a codename. The
 * app asks everyone questions privately and leaks ANONYMIZED clues; the room
 * works out who is who, and hunts the hidden Crook (a hidden Cop helps). The
 * design conviction: NO ONE is ever asked to lie. The app does the concealing;
 * the Crook and Cop simply withhold (stay silent about their role). No
 * elimination — everyone plays the whole session. See docs/spec-spot-the-counterfeit.md.
 */
return new class extends Migration {
    public function up(): void
    {
        // Leader-authored question bank. Each question the app asks privately can
        // become an anonymized public clue.
        Schema::create('mystery_questions', function (Blueprint $t) {
            $t->id();
            $t->text('prompt');
            $t->string('kind', 16)->default('getknow');   // getknow | value | scripture
            $t->json('options')->nullable();              // choices; null = short answer
            $t->boolean('clueable')->default(true);        // answer may surface as a clue
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
            $t->index(['kind', 'is_active']);
        });

        Schema::create('game_rooms', function (Blueprint $t) {
            $t->id();
            $t->string('code', 8)->unique();
            $t->string('host_token', 40)->index();
            $t->string('status', 16)->default('lobby');    // lobby|playing|accusation|revealed|ended
            $t->unsignedSmallInteger('round_no')->default(0);
            $t->unsignedSmallInteger('rounds_total')->default(8);
            $t->unsignedBigInteger('current_question_id')->nullable();
            $t->timestamp('current_question_started_at')->nullable();
            $t->json('settings')->nullable();
            $t->timestamps();
        });

        Schema::create('game_room_players', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_room_id')->index();
            $t->string('name', 60);
            $t->string('token', 40)->index();              // device identity (reuses cop_kid)
            $t->string('codename', 32)->nullable();
            $t->string('role', 12)->default('citizen');    // crook|cop|citizen
            $t->integer('score')->default(0);
            $t->timestamps();
        });

        Schema::create('mystery_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_room_id')->index();
            $t->unsignedSmallInteger('round_no');
            $t->foreignId('player_id')->index();
            $t->unsignedBigInteger('question_id')->nullable();
            $t->text('answer')->nullable();
            $t->boolean('stayed_silent')->default(false);  // the Crook/Cop's legitimate tool
            $t->timestamps();
        });

        Schema::create('mystery_clues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_room_id')->index();
            $t->unsignedSmallInteger('round_no');
            $t->foreignId('player_id')->index();           // whose answer this clue is about
            $t->text('text');
            $t->timestamp('revealed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('mystery_guesses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_room_id')->index();
            $t->foreignId('guesser_player_id')->index();
            $t->string('target_codename', 32);
            $t->foreignId('guessed_player_id')->nullable();
            $t->boolean('is_correct')->default(false);
            $t->timestamps();
        });

        Schema::create('mystery_investigations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_room_id')->index();
            $t->foreignId('cop_player_id')->index();
            $t->string('target_codename', 32);
            $t->boolean('result')->default(false);          // was the target the crook?
            $t->unsignedSmallInteger('round_no');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mystery_investigations');
        Schema::dropIfExists('mystery_guesses');
        Schema::dropIfExists('mystery_clues');
        Schema::dropIfExists('mystery_answers');
        Schema::dropIfExists('game_room_players');
        Schema::dropIfExists('game_rooms');
        Schema::dropIfExists('mystery_questions');
    }
};
