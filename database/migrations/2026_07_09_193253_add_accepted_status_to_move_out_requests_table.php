<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Adds 'accepted' as a distinct state between 'acknowledged' (landlord has
// seen it) and 'completed' (the system has processed the actual move-out).
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE move_out_requests MODIFY status ENUM('pending', 'acknowledged', 'accepted', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE move_out_requests SET status = 'acknowledged' WHERE status = 'accepted'");
        DB::statement("ALTER TABLE move_out_requests MODIFY status ENUM('pending', 'acknowledged', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};