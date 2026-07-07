<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Notes & Keys (Karlon 2026-07-06): private admin vault for credentials and
 *  operational details. Bodies encrypted at rest (Crypt/APP_KEY). */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_notes', function (Blueprint $t) {
            $t->id();
            $t->string('title', 120);
            $t->text('body');                 // encrypted payload
            $t->foreignId('created_by')->constrained('users');
            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('admin_notes'); }
};
