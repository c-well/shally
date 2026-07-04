<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Member comments on messages (Karlon 2026-07-04): signed-in members only,
 *  honeypot-guarded at the route. Soft deletes so moderation is reversible. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('message_comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sermon_id')->constrained('peace_sermons')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->text('body');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['sermon_id', 'created_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('message_comments'); }
};
