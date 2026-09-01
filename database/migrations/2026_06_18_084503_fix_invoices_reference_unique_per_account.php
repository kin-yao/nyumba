<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('invoices'))
            ->pluck('name')
            ->unique()
            ->toArray();

        Schema::table('invoices', function (Blueprint $table) use ($indexes) {
            // Drop old global unique constraint only if it still exists
            if (in_array('invoices_reference_unique', $indexes)) {
                $table->dropUnique('invoices_reference_unique');
            }

            // Add per-account unique constraint only if it doesn't exist yet
            if (!in_array('invoices_account_reference_unique', $indexes)) {
                $table->unique(['account_id', 'reference'], 'invoices_account_reference_unique');
            }
        });
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('invoices'))
            ->pluck('name')
            ->unique()
            ->toArray();

        Schema::table('invoices', function (Blueprint $table) use ($indexes) {
            if (in_array('invoices_account_reference_unique', $indexes)) {
                $table->dropUnique('invoices_account_reference_unique');
            }

            if (!in_array('invoices_reference_unique', $indexes)) {
                $table->unique('reference', 'invoices_reference_unique');
            }
        });
    }
};