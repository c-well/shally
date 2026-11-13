<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Real people vs bots, and where visitors come from.
 * confirmed_at: set when the visitor's browser actually runs the page (the
 *   tracker's "view" beacon or any click/scroll). Scrapers that load a page and
 *   leave never set it, so analytics counts confirmed rows only.
 * source: where the visit came from (google, facebook, email, direct, a site).
 * region / city: from the server's own GeoLite2 file. No IP leaves the server.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $t) {
            $t->timestamp('confirmed_at')->nullable()->index();
            $t->string('source', 32)->nullable()->index();
            $t->string('region', 64)->nullable();
            $t->string('city', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $t) {
            $t->dropIndex(['confirmed_at']);
            $t->dropIndex(['source']);
            $t->dropColumn(['confirmed_at', 'source', 'region', 'city']);
        });
    }
};
