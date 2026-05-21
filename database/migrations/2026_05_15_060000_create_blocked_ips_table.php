<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 200)->nullable();
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()
                ->comment('Auto-unblock at this time. NULL = permanent.');
            $table->integer('hits_at_block')->default(0)
                ->comment('Audit failure count when block was added — for after-the-fact triage');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
