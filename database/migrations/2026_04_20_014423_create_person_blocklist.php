<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('person_blocklist', function (Blueprint $table) {
            $table->id();
            $table->string('person', 180)->unique();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('person_blocklist');
    }
};
