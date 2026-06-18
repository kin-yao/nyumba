<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_reference_unique');
            $table->unique(['account_id', 'reference'], 'invoices_account_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_account_reference_unique');
            $table->unique('reference', 'invoices_reference_unique');
        });
    }
};