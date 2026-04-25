<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Going all-MiniSearch; drop the MySQL bible corpus + its FULLTEXT
        if (Schema::hasTable('bible_verses')) {
            Schema::dropIfExists('bible_verses');
        }
        // Keep hymns.body (needed to display hymn content in the lightbox) but drop FULLTEXT
        try { DB::statement('ALTER TABLE hymns DROP INDEX hymns_title_body_ft'); } catch (\Throwable $e) {}
    }

    public function down(): void {
        // We're not rebuilding MySQL search; no-op.
    }
};
