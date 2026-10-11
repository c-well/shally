<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The saved "normal week".
 *
 * startNextWeek() already carries last week forward verbatim, which is useful
 * but is not a baseline: each week inherits the previous week's additions, so
 * the thing being copied has itself already drifted. Andre asked for a floor —
 * somewhere to land after a few busy weeks of adding.
 *
 * Rows are append-only. The newest kind='default' row IS the active default;
 * older ones are its history, so a bad save can be rolled back. A kind='undo'
 * row is written immediately before a restore, holding what that bulletin
 * looked like a moment earlier, which is what the Undo button puts back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletin_defaults', function (Blueprint $t) {
            $t->id();

            // 'default' = a saved normal week. 'undo' = a pre-restore safety copy.
            $t->string('kind', 12)->default('default');

            // Only set on 'undo' rows — which bulletin the copy belongs to.
            $t->unsignedBigInteger('bulletin_id')->nullable();

            // { lines: [...], announcements: [...], meta: {...} }
            $t->json('payload');

            $t->string('note', 160)->nullable();
            $t->unsignedBigInteger('saved_by')->nullable();
            $t->timestamps();

            $t->index(['kind', 'created_at'], 'bulletin_defaults_kind_created_idx');
            $t->index(['bulletin_id', 'kind'], 'bulletin_defaults_bulletin_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_defaults');
    }
};
