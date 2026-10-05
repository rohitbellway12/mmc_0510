<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReasonToBookingIgnoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_ignores', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_ignores', 'reason')) {
                $table->text('reason')->nullable()->after('provider_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_ignores', function (Blueprint $table) {
            if (Schema::hasColumn('booking_ignores', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
}
