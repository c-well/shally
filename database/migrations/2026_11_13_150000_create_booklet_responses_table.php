<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* "Your Sabbath" answers left under the web booklet. Private: only the church reads them. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booklet_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bulletin_id')->constrained()->cascadeOnDelete();
            $t->json('picks')->nullable();
            $t->text('story')->nullable();
            $t->string('first_name', 40)->nullable();
            $t->string('ip_hash', 64)->nullable();
            $t->timestamps();
            $t->index(['bulletin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booklet_responses');
    }
};
