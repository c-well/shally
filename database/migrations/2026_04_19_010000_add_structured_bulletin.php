<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bulletins', function (Blueprint $t) {
            $t->timestamp('published_at')->nullable()->after('is_published');
            $t->json('published_snapshot')->nullable()->after('published_at');
        });

        Schema::create('bulletin_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bulletin_id')->constrained()->cascadeOnDelete();
            $t->string('section')->nullable();   // e.g. "Pastoral Greetings / Baby Dedication" — for grouping headers
            $t->string('part')->nullable();      // "Call to Worship", "Opening Hymn", etc. Nullable so a pure section-header row works.
            $t->string('person')->nullable();    // "Sis Keri-ann Genus", "#15 My Maker and My King"
            $t->string('kind', 32)->default('line'); // 'line' | 'section_header'
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['bulletin_id', 'sort_order']);
        });

        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bulletin_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('detail')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['bulletin_id', 'sort_order']);
        });

        Schema::create('hymns', function (Blueprint $t) {
            $t->unsignedSmallInteger('number')->primary();
            $t->string('title');
            $t->timestamps();
            $t->index('title');
        });
    }

    public function down(): void {
        Schema::dropIfExists('hymns');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('bulletin_lines');
        Schema::table('bulletins', function (Blueprint $t) {
            $t->dropColumn(['published_at', 'published_snapshot']);
        });
    }
};
