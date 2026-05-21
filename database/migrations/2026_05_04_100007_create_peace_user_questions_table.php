<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_user_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->nullable()->constrained('peace_sermons')->nullOnDelete()
                  ->comment('Which sermon page prompted the question (nullable for general questions)');
            $table->text('question');
            $table->string('asker_email', 200)->nullable();
            $table->boolean('asker_keep_anonymous')->default(true);
            $table->enum('status', [
                'pending',
                'answered_private',
                'published_as_qa',
                'discarded',
            ])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('admin_response')->nullable()->comment('Private email reply text, if sent');
            $table->foreignId('related_qa_id')->nullable()->constrained('peace_qa_pairs')->nullOnDelete()
                  ->comment('If promoted to a public Q&A, links to that row');

            // Spam defenses (same stack as /prayer + /contact)
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('sermon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_user_questions');
    }
};
