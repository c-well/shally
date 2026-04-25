<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $t) {
            $t->json('previous_published_snapshot')->nullable()->after('published_snapshot');
            $t->timestamp('previous_published_at')->nullable()->after('previous_published_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('bulletins', function (Blueprint $t) {
            $t->dropColumn(['previous_published_snapshot', 'previous_published_at']);
        });
    }
};
