<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();

            // Resolvable at webhook time from channel-level routing (which
            // property/account this endpoint belongs to), not from
            // tenant/invoice matching, which happens later. Nullable because
            // Pesalink central collection doesn't know the account until the
            // billRef is matched to a unit.
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();

            $table->string('provider');   // mpesa, kcb, equity, pesalink_collection
            $table->string('channel');    // mpesa | bank | pesalink
            $table->string('provider_transaction_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');

            // Exact inbound body, unmodified. Stored as the raw string, not
            // a re-encoded JSON structure, so it stays byte-identical to
            // what was received. Some providers (Pesalink) sign over the
            // exact raw bytes, and this preserves that for audit or any
            // future re-verification.
            $table->longText('raw_payload');

            $table->boolean('signature_valid')->default(false);
            $table->timestamp('received_at');
            $table->string('status')->default('RECEIVED');

            $table->timestamps();

            $table->unique(['provider', 'provider_transaction_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};