<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('total_amount');
            $table->string('transaction_id')->nullable()->after('payment_method');
            $table->boolean('is_paid')->default(0)->after('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'transaction_id', 'is_paid']);
        });
    }
};
