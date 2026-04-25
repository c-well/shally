<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("feedback_tickets", function (Blueprint $t) {
            $t->json("attachments")->nullable()->after("message");
        });
    }
    public function down(): void {
        Schema::table("feedback_tickets", function (Blueprint $t) {
            $t->dropColumn("attachments");
        });
    }
};
