<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// SMS-based report alerts (weekly/monthly/yearly to a set number) have been
// removed in favor of combined per-property PDF downloads on the Reports page.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'weekly_report_enabled', 'weekly_report_day', 'weekly_report_time',
                'monthly_report_enabled', 'monthly_report_day', 'monthly_report_time',
                'yearly_report_enabled', 'yearly_report_month', 'yearly_report_day', 'yearly_report_time',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('weekly_report_enabled')->default(false)->after('currency');
            $table->tinyInteger('weekly_report_day')->default(0)->after('weekly_report_enabled');
            $table->string('weekly_report_time', 5)->default('08:00')->after('weekly_report_day');

            $table->boolean('monthly_report_enabled')->default(false)->after('weekly_report_time');
            $table->tinyInteger('monthly_report_day')->default(1)->after('monthly_report_enabled');
            $table->string('monthly_report_time', 5)->default('08:00')->after('monthly_report_day');

            $table->boolean('yearly_report_enabled')->default(false)->after('monthly_report_time');
            $table->tinyInteger('yearly_report_month')->default(1)->after('yearly_report_enabled');
            $table->tinyInteger('yearly_report_day')->default(1)->after('yearly_report_month');
            $table->string('yearly_report_time', 5)->default('08:00')->after('yearly_report_day');
        });
    }
};