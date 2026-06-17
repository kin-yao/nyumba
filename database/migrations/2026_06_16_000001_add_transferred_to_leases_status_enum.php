<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires modifying the enum column directly
        DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active', 'ended', 'transferred') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leases MODIFY COLUMN status ENUM('active', 'ended') NOT NULL DEFAULT 'active'");
    }
};