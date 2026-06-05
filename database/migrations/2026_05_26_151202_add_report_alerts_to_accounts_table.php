<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Weekly report
            $table->boolean('weekly_report_enabled')->default(false)->after('currency');
            $table->tinyInteger('weekly_report_day')->default(0)->after('weekly_report_enabled'); // 0=Mon, 6=Sun
            $table->string('weekly_report_time', 5)->default('08:00')->after('weekly_report_day');

            // Monthly report
            $table->boolean('monthly_report_enabled')->default(false)->after('weekly_report_time');
            $table->tinyInteger('monthly_report_day')->default(1)->after('monthly_report_enabled'); // 1-28
            $table->string('monthly_report_time', 5)->default('08:00')->after('monthly_report_day');

            // Yearly report
            $table->boolean('yearly_report_enabled')->default(false)->after('monthly_report_time');
            $table->tinyInteger('yearly_report_month')->default(1)->after('yearly_report_enabled'); // 1-12
            $table->tinyInteger('yearly_report_day')->default(1)->after('yearly_report_month'); // 1-28
            $table->string('yearly_report_time', 5)->default('08:00')->after('yearly_report_day');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'weekly_report_enabled', 'weekly_report_day', 'weekly_report_time',
                'monthly_report_enabled', 'monthly_report_day', 'monthly_report_time',
                'yearly_report_enabled', 'yearly_report_month', 'yearly_report_day', 'yearly_report_time',
            ]);
        });
    }
};