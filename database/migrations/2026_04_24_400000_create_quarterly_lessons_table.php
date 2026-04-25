<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quarterly_lessons', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('year');
            $t->unsignedTinyInteger('quarter');             // 1-4
            $t->string('theme', 180);                        // "Growing in a Relationship With God"
            $t->date('starts_on');                           // first Saturday of the quarter
            $t->date('ends_on');                             // last Saturday
            $t->string('pdf_path', 255)->nullable();         // /lessons/en_2026t2.pdf  (local)
            $t->string('pdf_url', 500)->nullable();          // external fallback (e.g., fustero.es)
            $t->timestamps();
            $t->unique(['year', 'quarter']);
        });

        // Seed Q2 2026 with the file Karlon already uploaded
        DB::table('quarterly_lessons')->insert([
            'year'       => 2026,
            'quarter'    => 2,
            'theme'      => 'Growing in a Relationship With God',
            'starts_on'  => '2026-03-28',
            'ends_on'    => '2026-06-26',
            'pdf_path'   => 'lessons/en_2026t2.pdf',
            'pdf_url'    => 'https://www.fustero.es/en_2026t2.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('quarterly_lessons');
    }
};
