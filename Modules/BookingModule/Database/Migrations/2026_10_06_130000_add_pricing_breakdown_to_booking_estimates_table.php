<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricingBreakdownToBookingEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_estimates', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_estimates', 'rent_amount')) {
                $table->decimal('rent_amount', 10, 2)->default(0.00)->after('price');
            }
            if (!Schema::hasColumn('booking_estimates', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0.00)->after('rent_amount');
            }
            if (!Schema::hasColumn('booking_estimates', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->default(0.00)->after('delivery_fee');
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
            $columns = ['rent_amount', 'delivery_fee', 'security_deposit'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('booking_estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
