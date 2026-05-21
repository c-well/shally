<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_sermons', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_video_id', 20)->unique();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('speaker')->nullable();
            $table->date('sermon_date');

            // Pass 2 outputs (pastoral content for the page)
            $table->text('heart_line')->nullable()->comment('One italic-feeling line summarizing the message');
            $table->json('summary_paragraphs')->nullable()->comment('Array of 2-3 paragraph strings');

            // Audio
            $table->string('audio_url', 500)->nullable();
            $table->integer('audio_duration_seconds')->nullable();
            $table->enum('audio_status', ['pending', 'processing', 'ready', 'failed'])->default('pending');

            // Raw input
            $table->longText('transcript_raw')->nullable()->comment('Full timestamped transcript from yt-dlp');

            // Pass 1 boundary detection (decided 2026-05-04)
            $table->integer('sermon_start_seconds')->nullable()->comment('Auto by Pass 1; admin-overridable');
            $table->integer('sermon_end_seconds')->nullable();
            $table->decimal('boundary_confidence', 3, 2)->nullable()->comment('Pass 1 self-rated confidence 0.00-1.00');
            $table->text('boundary_reason')->nullable()->comment('Why Pass 1 picked these boundaries');
            $table->enum('boundary_source', ['auto', 'manual'])->default('auto');

            // Pipeline state
            $table->enum('processing_status', [
                'queued',
                'fetching_transcript',
                'detecting_boundaries',
                'fetching_audio',
                'generating',
                'validating',
                'published',
                'needs_review',
                'failed',
            ])->default('queued');
            $table->integer('processing_attempts')->default(0);
            $table->text('last_error')->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('published_at');
            $table->index('processing_status');
            $table->fullText(['title', 'heart_line']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_sermons');
    }
};
