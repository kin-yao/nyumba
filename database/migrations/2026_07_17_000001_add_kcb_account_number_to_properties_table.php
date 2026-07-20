<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // The KCB bank account number this property's rent lands in.
            // Always unique per property — matched against `creditAccountIdentifier`
            // in the IPN payload to resolve which property a notification belongs to.
            $table->string('kcb_account_number')->nullable()->after('mpesa_pull_registered_at');
            $table->timestamp('kcb_ipn_registered_at')->nullable()->after('kcb_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['kcb_account_number', 'kcb_ipn_registered_at']);
        });
    }
};