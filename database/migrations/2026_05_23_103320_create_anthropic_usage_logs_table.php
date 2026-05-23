<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('anthropic_usage_logs', function (Blueprint $t) {
            $t->id();
            $t->string('source', 64)->index();         // e.g. 'peace.content_generator', 'feedback.assistant'
            $t->string('model', 64);                    // e.g. 'claude-sonnet-4-5'
            $t->unsignedInteger('input_tokens');
            $t->unsignedInteger('output_tokens');
            $t->unsignedInteger('cache_read_tokens')->default(0);
            $t->unsignedInteger('cache_write_tokens')->default(0);
            $t->decimal('cost_usd', 10, 6);             // up to $9999.999999
            $t->string('request_id', 64)->nullable();
            $t->string('outcome', 16)->default('ok');   // 'ok' | 'error'
            $t->text('error_message')->nullable();
            $t->timestamps();
            $t->index('created_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('anthropic_usage_logs');
    }
};
