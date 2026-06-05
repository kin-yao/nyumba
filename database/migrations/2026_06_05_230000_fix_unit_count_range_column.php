<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // unit_count_range stores values like "1-5", "6-20" — must be a string.
            $table->string('unit_count_range', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->integer('unit_count_range')->nullable()->change();
        });
    }
};