<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBookingTypeColumnsToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('booking_type', ['normal', 'emergency'])->default('normal')->after('payment_method');
            $table->decimal('emergency_charge', 24, 3)->default(0)->after('booking_type');
            $table->uuid('selected_slot_id')->nullable()->after('emergency_charge');
            $table->text('damage_description')->nullable()->after('selected_slot_id');
            $table->string('car_registration_number', 50)->nullable()->after('damage_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_type',
                'emergency_charge',
                'selected_slot_id',
                'damage_description',
                'car_registration_number'
            ]);
        });
    }
}
