<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('car_bookings', 'booking_id')) {
                $table->foreignUuid('booking_id')->nullable()->after('user_id')->constrained('bookings')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('car_bookings', 'booking_id')) {
                $table->dropForeign(['booking_id']);
                $table->dropColumn('booking_id');
            }
        });
    }
};

