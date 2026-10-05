<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('car_bookings', 'pickup_coordinates')) {
                $table->json('pickup_coordinates')->nullable()->after('pickup_location')
                    ->comment('{"latitude": 22.7195687, "longitude": 75.8577258, "address": "Full address"}');
            }

            if (!Schema::hasColumn('car_bookings', 'drop_coordinates')) {
                $table->json('drop_coordinates')->nullable()->after('drop_location')
                    ->comment('{"latitude": 22.721755, "longitude": 75.801235, "address": "Full address"}');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->dropColumn('pickup_coordinates');
            $table->dropColumn('drop_coordinates');
        });
    }
};
