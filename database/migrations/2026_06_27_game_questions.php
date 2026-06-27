<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Questions for Verse Tetris (2026-06-27). A right answer earns a "re-morph"
 * (reshape the falling piece); a wrong answer raises the speed. Either way the
 * teaching line keeps the focus on Jesus.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_questions', function (Blueprint $t) {
            $t->id();
            $t->string('topic', 40)->default('Jesus');
            $t->text('question');
            $t->json('options');            // array of choices
            $t->unsignedTinyInteger('answer'); // index of the correct option
            $t->string('teaching')->nullable(); // short "why" / reference shown after
            $t->unsignedTinyInteger('difficulty')->default(1);
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
            $t->index(['topic', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_questions');
    }
};
