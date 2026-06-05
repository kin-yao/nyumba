<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('use_case')->nullable()->after('name');
            $table->unsignedInteger('unit_count_range')->nullable()->after('use_case'); // 5, 20, 50, 100, 999
            $table->string('recommended_plan')->nullable()->after('unit_count_range');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['use_case', 'unit_count_range', 'recommended_plan']);
        });
    }
};