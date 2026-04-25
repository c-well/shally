<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $t) {
            $t->id();
            $t->string('image_path', 255);                 // /uploads/slides/xyz.jpg
            $t->string('caption', 200)->nullable();        // Overlaid text (optional)
            $t->string('subcaption', 200)->nullable();     // Smaller line below caption
            $t->string('link_url', 500)->nullable();       // Optional click destination
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
