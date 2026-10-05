<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlotBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slot_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id');
            $table->foreignUuid('provider_time_slot_id');
            $table->date('booking_date');
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('provider_time_slot_id')->references('id')->on('provider_time_slots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('slot_bookings');
    }
}
