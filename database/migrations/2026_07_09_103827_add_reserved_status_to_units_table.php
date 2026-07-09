<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Adds 'reserved' — used when a landlord accepts a tenant's referral booking
// for the room. The unit is held for that referral once the current tenant
// moves out, rather than becoming plain 'vacant'.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE units MODIFY status ENUM('vacant', 'occupied', 'maintenance', 'reserved') DEFAULT 'vacant'");
    }

    public function down(): void
    {
        DB::statement("UPDATE units SET status = 'vacant' WHERE status = 'reserved'");
        DB::statement("ALTER TABLE units MODIFY status ENUM('vacant', 'occupied', 'maintenance') DEFAULT 'vacant'");
    }
};