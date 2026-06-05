<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', [
                'repairs', 'utilities', 'salaries',
                'supplies', 'insurance', 'land_rates',
                'professional_fees', 'marketing', 'other'
            ]);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->string('vendor')->nullable();
            $table->enum('payment_method', ['cash', 'mpesa', 'bank', 'cheque']);
            $table->string('reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};