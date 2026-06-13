<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('mpesa_shortcode')->nullable()->after('account_format');
            $table->string('mpesa_consumer_key')->nullable()->after('mpesa_shortcode');
            $table->text('mpesa_consumer_secret')->nullable()->after('mpesa_consumer_key'); // encrypted cast
            $table->string('mpesa_nominated_number')->nullable()->after('mpesa_consumer_secret');
            $table->timestamp('mpesa_c2b_registered_at')->nullable()->after('mpesa_nominated_number');
            $table->timestamp('mpesa_pull_registered_at')->nullable()->after('mpesa_c2b_registered_at');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'mpesa_shortcode',
                'mpesa_consumer_key',
                'mpesa_consumer_secret',
                'mpesa_nominated_number',
                'mpesa_c2b_registered_at',
                'mpesa_pull_registered_at',
            ]);
        });
    }
};