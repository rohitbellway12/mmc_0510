<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricingTypeToBookingEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_estimates', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_estimates', 'pricing_type')) {
                $table->string('pricing_type', 30)->nullable()->default('daily')->after('pickup_type'); // daily, hourly
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
        Schema::table('booking_estimates', function (Blueprint $table) {
            if (Schema::hasColumn('booking_estimates', 'pricing_type')) {
                $table->dropColumn('pricing_type');
            }
        });
    }
}
