<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('notes');     // paybill | till | null
            $table->string('business_number')->nullable()->after('payment_type');
            $table->string('till_number')->nullable()->after('business_number');
            $table->string('account_format')->nullable()->after('till_number'); // unit_number | tenant_name | phone_number
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['payment_type','business_number','till_number','account_format']);
        });
    }
};