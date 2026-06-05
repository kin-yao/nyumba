<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id')->nullable(); // null = system action
            $table->string('event');           // e.g. invoice.created, payment.recorded
            $table->string('description');     // human readable description
            $table->string('subject_type')->nullable();  // model class
            $table->unsignedBigInteger('subject_id')->nullable(); // model id
            $table->json('metadata')->nullable();  // extra data e.g. amount, reference
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['account_id', 'created_at']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};