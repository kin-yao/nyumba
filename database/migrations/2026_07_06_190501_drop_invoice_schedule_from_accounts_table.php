<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Automatic invoice scheduling moved from account-wide to per-property.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['auto_invoice_enabled', 'invoice_send_day']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('auto_invoice_enabled')->default(false);
            $table->tinyInteger('invoice_send_day')->default(1);
        });
    }
};