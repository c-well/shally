<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Convert enum('pending','closed') to varchar(20).
        // chat-Claude flagged enums as a trap (any future status addition
        // requires a destructive migration). varchar + app-level constants
        // is the better pattern.
        DB::statement("ALTER TABLE feedback_tickets MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    }
    public function down(): void
    {
        DB::statement("ALTER TABLE feedback_tickets MODIFY COLUMN status ENUM('pending','closed') NOT NULL DEFAULT 'pending'");
    }
};
