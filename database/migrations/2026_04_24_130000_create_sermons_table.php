<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sermons', function (Blueprint $t) {
            $t->id();
            $t->string('slug', 120)->unique();              // URL slug, auto-derived from title
            $t->string('title', 200);
            $t->string('speaker', 120)->nullable();         // Optional preacher name
            $t->date('preached_on');                        // The Sabbath it was preached
            $t->string('scripture_ref', 200)->nullable();   // "Isaiah 43:1-7" style
            $t->text('description_md')->nullable();         // Optional summary
            $t->text('description_html')->nullable();       // Cached render
            $t->string('audio_path', 255);                  // /uploads/sermons/xyz.mp3
            $t->unsignedInteger('audio_size_bytes')->default(0);
            $t->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index('preached_on');     // ordered listing
            $t->index('slug');            // fast slug lookup (already unique but explicit for clarity)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermons');
    }
};
