<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Anonymous thumbs-up reactions (per spec section 13)
        Schema::create('peace_reactions', function (Blueprint $table) {
            $table->id();
            $table->char('device_token', 36)->comment('UUID stored in cookie + localStorage');
            $table->enum('reactable_type', ['sermon', 'qa_pair', 'heart_line']);
            $table->unsignedBigInteger('reactable_id');
            $table->char('ip_hash', 64)->nullable()->comment('SHA-256 of IP for rate-limit only — purged after 7 days');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['device_token', 'reactable_type', 'reactable_id'], 'unique_device_reaction');
            $table->index(['reactable_type', 'reactable_id']);
            $table->index(['ip_hash', 'created_at']);
        });

        // Cached counts so page renders don't COUNT(*) every load
        Schema::create('peace_reaction_counts', function (Blueprint $table) {
            $table->enum('reactable_type', ['sermon', 'qa_pair', 'heart_line']);
            $table->unsignedBigInteger('reactable_id');
            $table->unsignedInteger('count')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->primary(['reactable_type', 'reactable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peace_reaction_counts');
        Schema::dropIfExists('peace_reactions');
    }
};
