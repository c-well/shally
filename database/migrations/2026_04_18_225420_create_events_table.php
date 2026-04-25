<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->dateTime('start_at');
            $t->dateTime('end_at')->nullable();
            $t->string('location')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('created_by')->constrained('users');
            $t->boolean('is_public')->default(true);
            $t->timestamps();
            $t->index(['start_at', 'is_public']);
        });
    }
    public function down(): void { Schema::dropIfExists('events'); }
};
