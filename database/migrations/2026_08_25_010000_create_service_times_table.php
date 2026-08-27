<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_times', function (Blueprint $t) {
            $t->id();
            $t->string('name');                      // "Sabbath School"
            $t->string('when_label');                // "SAT · 9:30 AM" — display only
            $t->string('where_label')->default('In person');
            $t->string('zoom_url')->nullable();      // null = gathered in person

            // The live window, as DATA rather than the PHP closure it used to be.
            // days holds Carbon dayOfWeek numbers (0=Sun … 6=Sat); the service is
            // "on now" between live_from and live_until on any of those days.
            $t->json('days');
            $t->time('live_from')->nullable();
            $t->time('live_until')->nullable();

            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_published')->default(true);
            $t->timestamps();
            $t->softDeletes();

            $t->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_times');
    }
};
