<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Announcements can carry an optional image and/or video link (2026-06-26).
 * Shown nested in the public bulletin — the newest expanded, older ones behind
 * a "More" toggle, media lazy-loaded on demand so the bulletin stays light.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $t) {
            $t->string('image_path')->nullable()->after('detail');
            $t->string('video_url')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', fn (Blueprint $t) => $t->dropColumn(['image_path', 'video_url']));
    }
};
