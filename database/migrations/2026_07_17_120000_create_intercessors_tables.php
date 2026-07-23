<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('intercessors', function (Blueprint $t) {
            $t->id();
            $t->string('name', 60);
            $t->string('phone', 20)->unique();
            $t->string('pin_hash');
            $t->enum('role', ['head', 'regular'])->default('regular');
            $t->foreignId('added_by_intercessor_id')->nullable()->constrained('intercessors')->nullOnDelete();
            $t->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('last_seen_at')->nullable();
            $t->ipAddress('last_ip')->nullable();
            $t->boolean('active')->default(true);
            $t->unsignedTinyInteger('pin_wrong_count')->default(0);
            $t->timestamp('pin_locked_until')->nullable();
            $t->timestamps();
        });

        Schema::create('intercessor_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('intercessor_id')->constrained()->cascadeOnDelete();
            $t->string('token_hash', 64)->unique();  // sha256 of the cookie token
            $t->ipAddress('last_ip')->nullable();
            $t->string('user_agent', 400)->nullable();
            $t->timestamp('last_seen_at')->nullable();
            $t->timestamp('expires_at');
            $t->timestamps();
        });

        Schema::create('intercessor_prayer_views', function (Blueprint $t) {
            $t->id();
            $t->foreignId('intercessor_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prayer_request_id')->constrained()->cascadeOnDelete();
            $t->timestamp('viewed_at');
            $t->unique(['intercessor_id', 'prayer_request_id']);
        });

        Schema::create('intercessor_prayer_prayed', function (Blueprint $t) {
            $t->id();
            $t->foreignId('intercessor_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prayer_request_id')->constrained()->cascadeOnDelete();
            $t->timestamp('prayed_at');
            $t->unique(['intercessor_id', 'prayer_request_id']);
        });

        Schema::create('intercessor_prayer_replies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('intercessor_id')->constrained()->cascadeOnDelete();
            $t->foreignId('prayer_request_id')->constrained()->cascadeOnDelete();
            $t->text('message');
            $t->string('sent_to', 200);
            $t->string('delivery_status', 20)->default('sent');
            $t->timestamp('sent_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('intercessor_prayer_replies');
        Schema::dropIfExists('intercessor_prayer_prayed');
        Schema::dropIfExists('intercessor_prayer_views');
        Schema::dropIfExists('intercessor_sessions');
        Schema::dropIfExists('intercessors');
    }
};
