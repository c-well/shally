<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Physical/digital announcement split (2026-07-04): printed bulletin carries only
 *  is_web_only=0 rows; the rest live on /announcements (reached by the printed QR). */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $t) {
            $t->boolean('is_web_only')->default(false)->after('sort_order');
        });
    }
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $t) { $t->dropColumn('is_web_only'); });
    }
};
