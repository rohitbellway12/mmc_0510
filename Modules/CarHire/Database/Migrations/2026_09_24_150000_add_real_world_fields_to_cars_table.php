<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRealWorldFieldsToCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cars', function (Blueprint $table) {
            if (!Schema::hasColumn('cars', 'mileage_limit')) {
                $table->string('mileage_limit')->nullable()->default('Unlimited');
            }
            if (!Schema::hasColumn('cars', 'extra_mileage_charge')) {
                $table->decimal('extra_mileage_charge', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('cars', 'fuel_policy')) {
                $table->string('fuel_policy')->nullable()->default('Full to Full');
            }
            if (!Schema::hasColumn('cars', 'delivery_fee')) {
                $table->decimal('delivery_fee', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('cars', 'min_driver_age')) {
                $table->integer('min_driver_age')->default(21);
            }
            if (!Schema::hasColumn('cars', 'min_booking_hours')) {
                $table->integer('min_booking_hours')->default(1);
            }
            if (!Schema::hasColumn('cars', 'luggage_capacity')) {
                $table->integer('luggage_capacity')->default(2);
            }
            if (!Schema::hasColumn('cars', 'amenities')) {
                $table->text('amenities')->nullable();
            }
            if (!Schema::hasColumn('cars', 'chauffeur_tier')) {
                $table->string('chauffeur_tier')->nullable()->default('business_class');
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
        Schema::table('cars', function (Blueprint $table) {
            $columns = [
                'mileage_limit',
                'extra_mileage_charge',
                'fuel_policy',
                'delivery_fee',
                'min_driver_age',
                'min_booking_hours',
                'luggage_capacity',
                'amenities',
                'chauffeur_tier',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cars', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
