<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handouts', function (Blueprint $t) {
            $t->id();
            $t->string('token', 16)->unique();
            $t->string('template', 24)->default('notice');

            $t->string('title');
            $t->string('eyebrow')->nullable();
            $t->text('body')->nullable();
            $t->string('link_url')->nullable();
            $t->string('link_label')->nullable();
            $t->string('image_path')->nullable();
            $t->string('theme', 24)->default('default');

            // Only the event/guest templates fill these in.
            $t->dateTime('happens_at')->nullable();
            $t->string('location')->nullable();

            // Lifespan. 'expires' dies on its own; 'open' stays live but nags.
            // There is no "forever" — see the Handout model docblock.
            $t->string('mode', 12)->default('expires');
            $t->dateTime('expires_at')->nullable();
            $t->unsignedSmallInteger('nudge_every_days')->default(30);
            $t->dateTime('nudged_at')->nullable();

            $t->unsignedInteger('views')->default(0);
            $t->unsignedInteger('uniques')->default(0);
            $t->dateTime('last_seen_at')->nullable();

            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['mode', 'expires_at']);
        });

        Schema::create('handout_visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('handout_id')->constrained()->cascadeOnDelete();
            // Salted daily hash of IP+UA — enough to count a person twice in a
            // day as once, not enough to follow them anywhere. Same posture as
            // the site's first-party analytics.
            $t->string('visitor_hash', 64);
            $t->string('referrer')->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['handout_id', 'visitor_hash']);
            $t->index(['handout_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handout_visits');
        Schema::dropIfExists('handouts');
    }
};
