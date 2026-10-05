<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderTimeSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_time_slots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(1);
            $table->integer('max_bookings')->default(1);
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->unique(['provider_id', 'day_of_week', 'start_time', 'end_time'], 'provider_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_time_slots');
    }
}
