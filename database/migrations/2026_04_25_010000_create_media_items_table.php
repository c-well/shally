<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("media_items", function (Blueprint $t) {
            $t->id();
            $t->enum("kind", ["image", "audio"])->default("image");
            $t->string("path", 500);                    // relative to public/, e.g. "uploads/sermon-covers/foo.jpg"
            $t->string("original_name", 255)->nullable();
            $t->string("mime", 100)->nullable();
            $t->unsignedInteger("width")->nullable();
            $t->unsignedInteger("height")->nullable();
            $t->unsignedBigInteger("size_bytes")->default(0);
            $t->unsignedBigInteger("uploaded_by_user_id")->nullable();
            $t->string("source_tag", 60)->nullable();   // "sermon-cover" | "slide" | "flyer" | etc. — for filtering
            $t->timestamps();
            $t->index(["kind", "created_at"]);
            $t->unique("path");
        });
    }
    public function down(): void { Schema::dropIfExists("media_items"); }
};
