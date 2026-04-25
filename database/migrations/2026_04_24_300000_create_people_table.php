<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Canonical list of names manually added via /admin/names (via the + button).
        // The autocomplete pool is the UNION of (this table) + (distinct bulletin_lines.person values).
        // This lets clerks pre-populate names BEFORE they appear on a bulletin.
        Schema::create('people', function (Blueprint $t) {
            $t->id();
            $t->string('name', 180)->unique();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
