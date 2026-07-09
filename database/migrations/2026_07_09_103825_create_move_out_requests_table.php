<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('move_out_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();

            $table->date('requested_move_out_date');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'acknowledged', 'completed', 'cancelled'])->default('pending');

            // Optional "hand the room to a friend" booking
            $table->string('referral_name')->nullable();
            $table->string('referral_phone')->nullable();
            $table->enum('referral_status', ['none', 'pending', 'accepted', 'declined'])->default('none');

            $table->text('landlord_notes')->nullable();
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('move_out_requests');
    }
};