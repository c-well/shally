<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('event', 60);                      // login_success, login_failed, logout,
                                                          // magic_link_request, magic_link_consume,
                                                          // error_500, error_404_app, etc.
            $t->text('description')->nullable();          // human-readable detail
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->string('url', 500)->nullable();
            $t->json('meta')->nullable();                 // arbitrary structured detail
            $t->timestamp('created_at')->useCurrent();    // logs are immutable, no updated_at

            $t->index(['event', 'created_at']);
            $t->index(['user_id', 'created_at']);
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
