<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['mpesa_transaction_id', 'mpesa_phone']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('mpesa_transaction_id')->nullable();
            $table->string('mpesa_phone', 20)->nullable();
        });
    }
};