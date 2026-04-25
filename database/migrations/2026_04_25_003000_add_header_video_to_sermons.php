<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table("sermons", function (Blueprint $t) {
            $t->string("header_text", 200)->nullable()->after("title");
            $t->string("video_url", 500)->nullable()->after("audio_size_bytes");
        });
    }
    public function down(): void {
        Schema::table("sermons", function (Blueprint $t) {
            $t->dropColumn(["header_text", "video_url"]);
        });
    }
};
