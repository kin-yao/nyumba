<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proof_of_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();

            // What the tenant says the payment was for
            $table->enum('payment_for', ['rent', 'deposit']);
            $table->unsignedTinyInteger('period_month')->nullable(); // only when payment_for = rent
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->enum('method', ['mpesa', 'cash', 'bank', 'cheque'])->nullable();

            // The pasted M-Pesa/bank confirmation message — free text, no
            // parsing/validation. The landlord reads and interprets this.
            $table->text('message');

            $table->enum('status', ['pending', 'verified', 'dismissed'])->default('pending');

            // Set once a landlord records an actual Payment from this claim —
            // the payments table itself is never touched by an unverified claim.
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proof_of_payments');
    }
};