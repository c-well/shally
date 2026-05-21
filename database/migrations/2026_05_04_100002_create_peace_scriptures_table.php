<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peace_scriptures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sermon_id')->constrained('peace_sermons')->cascadeOnDelete();
            $table->string('book', 50);
            $table->integer('chapter');
            $table->integer('verse_start');
            $table->integer('verse_end')->nullable();
            $table->string('reference_display', 100)->comment('e.g. "Joshua 1:9" or "1 Kings 19:4-8"');
            $table->text('verse_text')->comment('Verse text retrieved from bible-api');
            $table->string('translation', 20)->default('ESV');
            $table->boolean('validated')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['book', 'chapter', 'verse_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_scriptures');
    }
};
