<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('plan');
            $table->unsignedInteger('sms_credits_monthly')->default(0)->after('sms_credits');
            $table->timestamp('grace_period_ends_at')->nullable()->after('plan_expires_at');
            $table->timestamp('trial_ends_at')->nullable()->after('grace_period_ends_at');
            $table->timestamp('subscribed_at')->nullable()->after('trial_ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'billing_cycle',
                'sms_credits_monthly',
                'grace_period_ends_at',
                'trial_ends_at',
                'subscribed_at',
            ]);
        });
    }
};