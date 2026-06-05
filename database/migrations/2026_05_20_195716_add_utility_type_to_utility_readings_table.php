<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            if (!Schema::hasColumn('utility_readings', 'utility_type')) {
                $table->enum('utility_type', ['water', 'electricity', 'other'])
                      ->default('water')
                      ->after('account_id');
            }
            $table->unique(['unit_id', 'utility_type', 'reading_month', 'reading_year'], 'utility_readings_unique');
        });
    }
    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropUnique('utility_readings_unique');
            if (Schema::hasColumn('utility_readings', 'utility_type')) {
                $table->dropColumn('utility_type');
            }
        });
    }
};