<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("bulletins", function (Blueprint $t) {
            $t->softDeletes()->after("event_ends_at");
        });
    }
    public function down(): void {
        Schema::table("bulletins", function (Blueprint $t) {
            $t->dropSoftDeletes();
        });
    }
};
