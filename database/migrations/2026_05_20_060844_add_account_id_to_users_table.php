<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('account_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', ['owner', 'read_only'])
                  ->default('owner')
                  ->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'phone', 'role']);
        });
    }
};