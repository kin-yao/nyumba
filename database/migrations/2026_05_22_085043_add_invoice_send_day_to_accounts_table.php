<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('invoice_send_day')->default(1)->after('plan_expires_at');
            $table->boolean('auto_invoice_enabled')->default(false)->after('invoice_send_day');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['invoice_send_day', 'auto_invoice_enabled']);
        });
    }
};