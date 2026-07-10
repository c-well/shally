<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Web-push subscriptions (clerk devices) — new prayer/contact → phone banner + live badge. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->text('endpoint');
            $t->string('endpoint_hash', 64)->unique();
            $t->string('p256dh', 255);
            $t->string('auth', 255);
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('push_subscriptions'); }
};
