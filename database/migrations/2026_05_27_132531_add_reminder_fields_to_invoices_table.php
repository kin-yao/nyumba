<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('reminder_3day_sent_at')->nullable()->after('sent_at');
            $table->timestamp('reminder_due_sent_at')->nullable()->after('reminder_3day_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['reminder_3day_sent_at', 'reminder_due_sent_at']);
        });
    }
};