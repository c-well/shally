<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add read tracking to prayer_requests
        Schema::table('prayer_requests', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('user_agent');
        });

        // Persist public contact form submissions so admin can see them in the inbox
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 200);
            $table->text('message');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::table('prayer_requests', fn (Blueprint $t) => $t->dropColumn('read_at'));
        Schema::dropIfExists('contact_messages');
    }
};
