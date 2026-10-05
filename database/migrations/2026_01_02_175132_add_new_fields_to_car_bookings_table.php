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
            $table->string('pickup_type')->nullable()->after('drop_location'); // self, delivery
            $table->time('pickup_time')->nullable()->after('pickup_type');
            $table->time('drop_time')->nullable()->after('pickup_time');
            $table->string('delivery_address')->nullable()->after('drop_time');
            $table->text('description')->nullable()->after('delivery_address');
            if (!Schema::hasColumn('car_bookings', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_type', 'pickup_time', 'drop_time', 'delivery_address', 'description']);
        });
    }
};
