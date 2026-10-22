<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Music day: singers send their track through one link, and André can see
 * who is in, what they are singing, and whose track is still missing.
 *
 * Before this it came by email, which loses the one thing he actually needs:
 * a running list of who has sent what.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_days', function (Blueprint $t) {
            $t->id();
            $t->string('title', 120)->default('Music Day');
            $t->date('service_date');
            /* The link IS the permission. Long and random, never an id. */
            $t->string('token', 40)->unique();
            $t->boolean('is_open')->default(true);
            $t->text('note')->nullable();
            $t->timestamp('files_purged_at')->nullable();
            $t->timestamps();
        });

        Schema::create('music_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('music_day_id')->constrained()->cascadeOnDelete();
            $t->string('name', 120);
            $t->string('song_title', 200)->nullable();
            /* upload | youtube | bringing */
            $t->string('method', 12);
            /* cd | usb, only when bringing it in person */
            $t->string('medium', 8)->nullable();
            $t->string('source_url', 500)->nullable();
            $t->string('file_path', 255)->nullable();
            $t->string('original_name', 200)->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->unsignedBigInteger('size_bytes')->nullable();
            /* queued | fetching | ready | failed | bringing */
            $t->string('status', 12)->default('queued');
            $t->string('error', 255)->nullable();
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->unsignedInteger('sort_order')->default(0);
            $t->string('ip_hash', 64)->nullable();
            $t->timestamps();

            $t->index(['music_day_id', 'sort_order']);
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('music_entries');
        Schema::dropIfExists('music_days');
    }
};
