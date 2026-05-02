<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('body');
            $table->boolean('want_followup')->default(false);
            $table->boolean('keep_private')->default(true);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('prayer_requests'); }
};
