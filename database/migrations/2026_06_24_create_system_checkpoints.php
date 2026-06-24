<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restore points for the changelog + rollback UI (2026-06-24).
 *
 * A checkpoint is a captured last-known-good state: the git SHA, a copy of
 * composer.lock, and a DB dump. The self-update routine writes one before each
 * update; admins can also create one manually before risky changes. The
 * changelog UI lists them and lets authorized users roll back to one.
 *
 * can_rollback on users: super_admins can always roll back; this flag grants it
 * to others (the "anyone we choose to assign" Karlon described).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_checkpoints', function (Blueprint $t) {
            $t->id();
            $t->string('label');                              // human description
            $t->string('kind', 24)->default('manual');        // manual | auto_update | pre_rollback
            $t->string('git_sha', 64)->nullable();
            $t->string('composer_lock_path')->nullable();
            $t->string('db_backup_path')->nullable();
            $t->string('app_version', 32)->nullable();        // Laravel version at capture
            $t->foreignId('created_by')->nullable();          // user id, or null = system
            $t->timestamp('restored_at')->nullable();         // set when this checkpoint was restored TO
            $t->foreignId('restored_by')->nullable();
            $t->timestamps();
            $t->index(['kind', 'created_at']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->boolean('can_rollback')->default(false)->after('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_checkpoints');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('can_rollback'));
    }
};
