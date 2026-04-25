<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_members', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->string('name', 180);
            $t->string('phone', 40)->nullable();
            $t->string('email', 180)->nullable();
            $t->boolean('is_active')->default(true);
            $t->integer('sort_order')->default(0);
            $t->timestamps();
            $t->index(['department_id', 'is_active', 'sort_order']);
        });
    }
    public function down(): void { Schema::dropIfExists('department_members'); }
};
