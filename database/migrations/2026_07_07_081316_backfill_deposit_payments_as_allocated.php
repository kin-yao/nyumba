<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Deposit payments were being created with is_allocated=false and never
// flipped to true, since only rent-invoice allocation set that flag.
// Deposits don't allocate against invoices, so they should read as settled
// as soon as they're received.
return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->where('payment_type', 'deposit')
            ->update(['is_allocated' => true]);
    }

    public function down(): void
    {
        // Not reversible — we don't know which deposit rows were
        // legitimately unallocated before this fix (none were).
    }
};