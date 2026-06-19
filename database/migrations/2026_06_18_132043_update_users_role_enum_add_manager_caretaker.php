<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing read_only users to caretaker
        DB::statement("UPDATE users SET role = 'caretaker' WHERE role = 'read_only'");

        // Change enum to include manager and caretaker
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','manager','caretaker') NOT NULL DEFAULT 'owner'");
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'owner' WHERE role = 'manager'");
        DB::statement("UPDATE users SET role = 'read_only' WHERE role = 'caretaker'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner','read_only') NOT NULL DEFAULT 'owner'");
    }
};