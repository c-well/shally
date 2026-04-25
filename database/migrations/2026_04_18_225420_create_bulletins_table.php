<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bulletins', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->date('service_date');
            $t->longText('body')->nullable();
            $t->string('pdf_path')->nullable();
            $t->boolean('is_published')->default(false);
            $t->foreignId('author_id')->constrained('users');
            $t->timestamps();
            $t->index(['is_published', 'service_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('bulletins'); }
};
