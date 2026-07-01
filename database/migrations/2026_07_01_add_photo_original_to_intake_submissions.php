<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Keep the pristine uploaded photo so image edits (rotate/crop) are non-destructive. */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('intake_submissions', function (Blueprint $t) {
            $t->string('photo_original_path')->nullable()->after('photo_path');
        });
    }
    public function down(): void
    {
        Schema::table('intake_submissions', function (Blueprint $t) {
            $t->dropColumn('photo_original_path');
        });
    }
};
