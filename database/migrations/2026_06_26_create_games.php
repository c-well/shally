<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kids Scripture games (2026-06-26).
 *
 * The point is to bring the Word before children, not to entertain — every
 * level is a real verse. A level = a verse + a game type + an age band, all
 * admin-editable. Players are just a name (identified by a localStorage token),
 * progress autosaves, and gentle stars encourage doing better — not competing.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_levels', function (Blueprint $t) {
            $t->id();
            $t->string('game_type', 24);                 // word_search | memory_match | hidden_words
            $t->string('age_band', 12);                  // little (4-6) | older (7-9) | teens
            $t->string('book', 40);                      // Bible book
            $t->string('reference', 60);                 // e.g. "John 3:16"
            $t->text('verse_text');
            $t->string('title')->nullable();
            $t->unsignedTinyInteger('difficulty')->default(1);
            $t->json('config')->nullable();
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable();
            $t->integer('sort_order')->default(0);
            $t->timestamps();
            $t->index(['game_type', 'age_band', 'is_active']);
        });

        Schema::create('game_players', function (Blueprint $t) {
            $t->id();
            $t->string('name', 60);
            $t->string('token', 40)->unique();           // localStorage identity (no login for kids)
            $t->unsignedInteger('total_stars')->default(0);
            $t->timestamps();
        });

        Schema::create('game_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('game_player_id')->index();
            $t->foreignId('game_level_id')->index();
            $t->json('state')->nullable();               // resume / autosave
            $t->unsignedInteger('best_score')->default(0);
            $t->unsignedTinyInteger('stars')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['game_player_id', 'game_level_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_progress');
        Schema::dropIfExists('game_players');
        Schema::dropIfExists('game_levels');
    }
};
