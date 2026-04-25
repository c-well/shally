<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedback_tickets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->enum('tag', ['bug', 'idea', 'question', 'note'])->default('note');
            $t->text('message');
            $t->text('claude_reply')->nullable();
            $t->enum('status', ['pending', 'closed'])->default('pending');
            $t->timestamp('closed_at')->nullable();
            $t->text('closed_note')->nullable();
            $t->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['user_id', 'status', 'created_at']);
            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_tickets');
    }
};
