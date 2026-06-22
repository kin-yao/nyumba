<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: expand enum to include all values including read_only so existing data stays valid
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','manager','caretaker','read_only') NOT NULL DEFAULT 'owner'");

        // Step 2: migrate read_only to caretaker now that caretaker is a valid enum value
        DB::statement("UPDATE users SET role = 'caretaker' WHERE role = 'read_only'");

        // Step 3: remove read_only from enum now that no rows use it
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','manager','caretaker') NOT NULL DEFAULT 'owner'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','manager','caretaker','read_only') NOT NULL DEFAULT 'owner'");
        DB::statement("UPDATE users SET role = 'read_only' WHERE role = 'caretaker'");
        DB::statement("UPDATE users SET role = 'owner' WHERE role = 'manager'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','read_only') NOT NULL DEFAULT 'owner'");
    }
};