<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('app_settings', function (Blueprint $t) {
            $t->string('key', 64)->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });
        \DB::table('app_settings')->insert([
            'key' => 'site_theme',
            'value' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public function down(): void { Schema::dropIfExists('app_settings'); }
};
