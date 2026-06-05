<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('reading_month');
            $table->unsignedSmallInteger('reading_year');
            $table->decimal('previous_reading', 10, 2)->default(0);
            $table->decimal('current_reading', 10, 2);
            $table->decimal('units_consumed', 10, 2)->default(0);
            $table->decimal('rate_per_unit', 8, 2);
            $table->decimal('charge_amount', 10, 2);
            $table->timestamps();

            $table->unique(['unit_id', 'reading_month', 'reading_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_readings');
    }
};