<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTyreIdToBookingRepeatDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('booking_repeat_details', function (Blueprint $table) {
            $table->uuid('tyre_id')->nullable()->after('service_id');
        });
    }

    public function down()
    {
        Schema::table('booking_repeat_details', function (Blueprint $table) {
            $table->dropColumn('tyre_id');
        });
    }
}
