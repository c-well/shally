<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Guest follow-up system, phase 1 (spec 2026-07-08): the digital guestbook
 *  and the sequence engine that remembers who to follow up with, and when. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $t) {
            $t->id();
            $t->string('name', 120);
            $t->string('phone', 40)->nullable();
            $t->string('email', 200)->nullable();
            $t->unsignedTinyInteger('birthday_month')->nullable();
            $t->unsignedTinyInteger('birthday_day')->nullable();
            $t->boolean('wants_updates')->default(false);
            $t->boolean('wants_volunteer')->default(false);
            $t->date('visited_on');
            $t->text('notes')->nullable();
            $t->string('ip_hash', 64)->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('guest_followups', function (Blueprint $t) {
            $t->id();
            $t->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $t->string('kind', 30);                    // thanks | questions | birthday | custom
            $t->date('due_on');
            $t->string('status', 20)->default('pending'); // pending | sent | skipped | failed
            $t->string('channel', 10)->nullable();     // sms | email (decided at send)
            $t->text('body')->nullable();              // null = engine template; set = personalized
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->index(['status', 'due_on']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('guest_followups');
        Schema::dropIfExists('guests');
    }
};
