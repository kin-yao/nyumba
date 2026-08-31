<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('bank_code', 30)->nullable()->after('till_number');
            $table->string('bank_account_number', 30)->nullable()->after('bank_code');
        });

        // Backfill from the old KCB-only column. kcb_account_number itself
        // is left in place for now (not dropped here) so this migration
        // can't lose data even if something downstream isn't right yet.
        \DB::table('properties')
            ->whereNotNull('kcb_account_number')
            ->update([
                'bank_code'           => 'kcb',
                'bank_account_number' => \DB::raw('kcb_account_number'),
            ]);
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['bank_code', 'bank_account_number']);
        });
    }
};