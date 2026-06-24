<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content revision history (2026-06-24).
 *
 * Per-record version trail for high-churn editable content (bulletins,
 * bulletin lines, pages, quarterly lessons). Every Eloquent update on a model
 * using the HasRevisions trait writes one row here holding the PRIOR values of
 * the fields that changed — so any single edit can be undone on its own,
 * without the all-or-nothing DB restore that system_checkpoints does.
 *
 * This is the "50 quick text edits, one at a time" tool. system_checkpoints
 * stays the "one major structural thing" tool. Two scales, two mechanisms.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $t) {
            $t->id();
            $t->string('revisionable_type');
            $t->unsignedBigInteger('revisionable_id');
            $t->foreignId('user_id')->nullable();         // who made the edit (null = system)
            $t->string('label');                           // human name of the record at edit time
            $t->string('summary');                         // "Edited title, body"
            $t->json('snapshot');                          // prior values of the changed fields
            $t->timestamps();
            $t->index(['revisionable_type', 'revisionable_id', 'id']);
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
