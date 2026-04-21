<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin', 'owner') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        // Downgrade: reset any owner users to 'user' first
        DB::statement("UPDATE users SET role = 'user' WHERE role = 'owner'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user'");
    }
};
