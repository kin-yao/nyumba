<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = collect(DB::select('SHOW INDEX FROM utility_readings'))
            ->pluck('Key_name')
            ->contains('utility_readings_unit_id_reading_month_reading_year_unique');

        if ($exists) {
            Schema::table('utility_readings', function (Blueprint $table) {
                $table->dropUnique('utility_readings_unit_id_reading_month_reading_year_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->unique(['unit_id', 'reading_month', 'reading_year']);
        });
    }
};
