<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_user_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('peace_subscribers')->cascadeOnDelete();
            $table->enum('type', ['question', 'experience'])->index();
            $table->text('body');
            $table->foreignId('related_sermon_id')->nullable()->constrained('peace_sermons')->nullOnDelete();
            $table->foreignId('related_topic_id')->nullable()->constrained('peace_topics')->nullOnDelete();
            $table->enum('attribution', ['anonymous', 'first_name_city'])->default('anonymous');
            $table->string('attribution_display', 100)->nullable()
                ->comment('e.g. "Karlon, NY" — only if attribution=first_name_city');
            $table->enum('status', ['pending', 'approved', 'archived', 'replied'])->default('pending')->index();
            $table->text('pastor_reply')->nullable();
            $table->timestamp('pastor_replied_at')->nullable();
            $table->foreignId('pastor_replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('ua_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_user_submissions');
    }
};
