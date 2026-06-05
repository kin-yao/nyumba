<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utility_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['water', 'garbage', 'electricity', 'other']);
            $table->decimal('amount', 10, 2);
            $table->enum('billing_type', ['per_unit', 'flat_fee', 'per_meter_reading']);
            $table->boolean('active')->default(true);
            $table->boolean('auto_bill')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utility_rates');
    }
};