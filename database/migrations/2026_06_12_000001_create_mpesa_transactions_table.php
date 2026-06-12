<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('subscription'); // subscription | sms_credits (future)
            $table->string('plan')->nullable();               // starter | growth | pro
            $table->string('billing_cycle')->default('monthly'); // monthly | yearly
            $table->decimal('amount', 10, 2);
            $table->string('phone', 15);
            $table->string('checkout_request_id')->nullable()->index();
            $table->string('merchant_request_id')->nullable();
            $table->string('mpesa_receipt')->nullable();
            $table->string('status')->default('pending'); // pending | success | failed | cancelled
            $table->string('result_code')->nullable();
            $table->text('result_desc')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};