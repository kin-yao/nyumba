<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires modifying the enum column directly. SQLite (used
        // for fast in-memory testing) doesn't enforce ENUM as a real type
        // in the first place — the column already accepts any string
        // there, so there's nothing to alter, only MySQL needs this.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active', 'ended', 'transferred') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active', 'ended') NOT NULL DEFAULT 'active'");
        }
    }
};