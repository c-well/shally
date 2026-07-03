<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user usage learning for the admin hub "hush latch" (2026-07-03).
 * item_key '__visits' counts hub loads; every other row counts clicks on a card.
 * After the 7th visit the hub grows a personal "most used" latch, auto-open.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_hub_usage', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->index();
            $t->string('item_key', 64);
            $t->unsignedInteger('clicks')->default(0);
            $t->timestamps();
            $t->unique(['user_id', 'item_key']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('admin_hub_usage');
    }
};
