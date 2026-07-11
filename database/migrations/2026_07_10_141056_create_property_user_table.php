<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Owners always have full account access and never need rows here.
// Managers/caretakers only see properties explicitly assigned via this table
// — no rows means no properties, by design (see Controller::filteredPropertyIds).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['property_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_user');
    }
};