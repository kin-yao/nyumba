<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('auto_invoice_enabled')->default(false)->after('notes');
            $table->tinyInteger('invoice_send_day')->default(1)->after('auto_invoice_enabled'); // 1-28
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['auto_invoice_enabled', 'invoice_send_day']);
        });
    }
};