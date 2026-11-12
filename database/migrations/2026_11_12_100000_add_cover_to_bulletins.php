<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A cover for the booklet print of a bulletin.
 *
 * Most weeks print flat, so most bulletins never set this, and the booklet
 * falls back to a plain cover: the church, the title, the date. A special
 * Sabbath, like Pathfinder and Adventurer Day, sets its own:
 *   { kicker, title, theme, theme_ref, emblems: [file names] }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $t) {
            $t->json('cover')->nullable()->after('pdf_font_color');
        });
    }

    public function down(): void
    {
        Schema::table('bulletins', fn (Blueprint $t) => $t->dropColumn('cover'));
    }
};
