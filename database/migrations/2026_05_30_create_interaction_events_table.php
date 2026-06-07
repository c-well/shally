<?php
/**
 * Native-built interaction tracking — no third-party analytics.
 *
 * page_views (existing) handles pageviews. This table captures:
 *   - clicks (x/y coords + element selector)
 *   - scroll_depth (max % reached per session)
 *   - hover_dwell (sampled, lighter signal)
 *   - named events (qa_open, audio_play_30s, bulletin_pdf, subscriber_add, etc.)
 *
 * Privacy: ip_hash uses same SHA256+app_key approach as page_views.
 * No raw IPs, no PII captured. Respects DNT header.
 *
 * Volume control: rows older than 30 days get rolled up daily into
 * heatmap_summaries + scroll_summaries + event_counters, then purged.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interaction_events', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('event_type', 32);              // click | scroll | hover | qa_open | audio_play_30s | pdf_download | subscribe | etc.
            $t->string('path', 255);                    // page where it happened
            $t->string('session_id', 64)->index();      // tied to page_views.session_id
            $t->string('ip_hash', 64)->nullable();
            $t->string('country', 8)->nullable();
            $t->string('device', 16)->nullable();       // desktop | mobile | tablet
            $t->unsignedSmallInteger('viewport_w')->nullable();
            $t->unsignedSmallInteger('viewport_h')->nullable();
            $t->unsignedSmallInteger('x')->nullable();  // clicks/hovers
            $t->unsignedSmallInteger('y')->nullable();
            $t->unsignedTinyInteger('scroll_pct')->nullable();  // 0–100
            $t->string('element_selector', 255)->nullable();
            $t->string('element_text', 160)->nullable();
            $t->json('meta')->nullable();               // arbitrary key/value
            $t->boolean('is_bot')->default(false);
            $t->timestamp('created_at')->useCurrent();

            $t->index(['event_type', 'path', 'created_at'], 'idx_event_path_time');
            $t->index(['event_type', 'created_at'], 'idx_event_time');
            $t->index('created_at');
        });

        // Rolled-up summaries — survive past the 30-day raw-event retention
        Schema::create('heatmap_summaries', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('path', 255);
            $t->date('day');
            $t->string('element_selector', 255);
            $t->string('element_text', 160)->nullable();
            $t->unsignedInteger('click_count');
            $t->unsignedInteger('hover_count');
            $t->timestamps();
            $t->unique(['path', 'day', 'element_selector'], 'uniq_heat');
            $t->index('day');
        });

        Schema::create('scroll_summaries', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('path', 255);
            $t->date('day');
            $t->unsignedTinyInteger('bucket');           // 10, 25, 50, 75, 100 (%)
            $t->unsignedInteger('session_count');         // sessions that hit this bucket
            $t->timestamps();
            $t->unique(['path', 'day', 'bucket'], 'uniq_scroll');
            $t->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scroll_summaries');
        Schema::dropIfExists('heatmap_summaries');
        Schema::dropIfExists('interaction_events');
    }
};
