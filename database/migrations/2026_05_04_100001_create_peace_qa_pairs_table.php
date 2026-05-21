<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_qa_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('peace_sermons')->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->integer('display_order')->default(0);
            $table->decimal('confidence_score', 3, 2)->nullable()->comment('Claude self-rating 0.00-1.00');
            $table->timestamps();

            $table->fullText(['question', 'answer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_qa_pairs');
    }
};
