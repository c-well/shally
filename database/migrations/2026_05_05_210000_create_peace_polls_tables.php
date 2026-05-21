<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_polls', function (Blueprint $table) {
            $table->id();
            $table->string('question', 500);
            // What scripture moment this poll connects to (display only — not validated)
            $table->string('scripture_ref', 100)->nullable();
            $table->text('scripture_explainer'); // shown after answer regardless of choice
            // Optional bind to a specific topic for topic-aware rotation
            $table->foreignId('topic_id')->nullable()->constrained('peace_topics')->nullOnDelete();
            // Optional bind to a specific sermon (overrides topic match)
            $table->foreignId('sermon_id')->nullable()->constrained('peace_sermons')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('display_priority')->default(0);
            $table->integer('rotation_days')->default(14)->comment('How long this poll stays in active rotation before deactivating');
            $table->timestamp('active_from')->nullable();
            $table->timestamp('active_until')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'topic_id']);
        });

        Schema::create('peace_poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('peace_polls')->cascadeOnDelete();
            $table->string('option_text', 300);
            $table->boolean('is_canonical')->default(false)->comment('The "scripture\'s answer" — shown after submit');
            $table->integer('display_order')->default(0);
            $table->integer('response_count')->default(0)->comment('Denormalized count for fast display');
            $table->timestamps();
            $table->index(['poll_id', 'display_order']);
        });

        Schema::create('peace_poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained('peace_polls')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('peace_poll_options')->cascadeOnDelete();
            $table->string('ip_hash', 64)->index()->comment('SHA-256 of IP+app_key (privacy: not raw IP)');
            $table->string('ua_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['poll_id', 'ip_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_poll_responses');
        Schema::dropIfExists('peace_poll_options');
        Schema::dropIfExists('peace_polls');
    }
};
