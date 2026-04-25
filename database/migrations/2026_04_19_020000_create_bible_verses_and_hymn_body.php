<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('bible_verses', function (Blueprint $t) {
            $t->id();
            $t->string('translation', 8);          // 'kjv' | 'esv'
            $t->string('book', 40);
            $t->unsignedSmallInteger('chapter');
            $t->unsignedSmallInteger('verse');
            $t->text('text');
            $t->index(['translation', 'book', 'chapter', 'verse']);
        });
        // FULLTEXT index on the text column for fast search
        DB::statement('ALTER TABLE bible_verses ADD FULLTEXT INDEX bible_verses_text_ft (text)');

        Schema::table('hymns', function (Blueprint $t) {
            $t->longText('body')->nullable()->after('title');
        });
        DB::statement('ALTER TABLE hymns ADD FULLTEXT INDEX hymns_title_body_ft (title, body)');
    }

    public function down(): void {
        DB::statement('ALTER TABLE hymns DROP INDEX hymns_title_body_ft');
        Schema::table('hymns', function (Blueprint $t) {
            $t->dropColumn('body');
        });
        Schema::dropIfExists('bible_verses');
    }
};
