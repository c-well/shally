<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('duty_assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->date('week_date'); // Saturday of the week
            $t->foreignId('member_id')->nullable()->constrained('department_members')->nullOnDelete();
            $t->string('role', 80)->nullable(); // for Platform Party: "Call to Worship", "Welcome", etc.
            $t->string('text_value', 240)->nullable(); // free text (e.g. sermon title) when no member
            $t->integer('sort_order')->default(0);
            $t->timestamps();
            $t->index(['department_id', 'week_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('duty_assignments'); }
};
