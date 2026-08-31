<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // The HMAC-SHA1 key IPSL signs IPN payloads with, per property
            // since each landlord's bank account is its own direct
            // integration with its own password.
            $table->text('ipsl_password')->nullable()->after('kcb_account_number');
        });

        Schema::table('units', function (Blueprint $table) {
            // Landlord's own payment reference for Pesalink (e.g. "KLM-A1").
            // Falls back to the unit's name if not set.
            $table->string('payment_reference')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('ipsl_password');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
};