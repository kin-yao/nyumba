<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('move_in_date');
            $table->date('move_out_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_required', 10, 2);
            $table->decimal('deposit_paid', 10, 2)->default(0);
            $table->decimal('escalation_percentage', 5, 2)->nullable();
            $table->date('next_review_date')->nullable();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};