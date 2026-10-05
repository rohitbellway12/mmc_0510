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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('car_model')->nullable()->after('car_registration_number');
            $table->string('car_color')->nullable()->after('car_model');
            $table->text('special_conditions')->nullable()->after('car_color');
            $table->text('notes')->nullable()->after('special_conditions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['car_model', 'car_color', 'special_conditions', 'notes']);
        });
    }
};
