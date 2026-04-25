<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("page_views", function (Blueprint $t) {
            $t->id();
            $t->string("path", 500);
            $t->string("referrer", 500)->nullable();
            $t->string("user_agent", 500)->nullable();
            $t->string("ip_hash", 64)->nullable();           // sha256 of IP for privacy (no raw IPs stored)
            $t->string("country", 2)->nullable();             // from CF-IPCountry header if behind Cloudflare
            $t->string("session_id", 64)->nullable();         // random cookie for unique-visitor counting
            $t->string("device", 12)->nullable();             // mobile | tablet | desktop
            $t->string("browser", 24)->nullable();            // chrome | safari | firefox | edge | other
            $t->timestamp("viewed_at")->useCurrent();
            $t->index(["path", "viewed_at"]);
            $t->index("viewed_at");
            $t->index("session_id");
        });
    }
    public function down(): void { Schema::dropIfExists("page_views"); }
};
