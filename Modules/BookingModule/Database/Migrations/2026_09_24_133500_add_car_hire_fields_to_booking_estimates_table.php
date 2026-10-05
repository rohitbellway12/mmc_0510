<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCarHireFieldsToBookingEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_estimates', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_estimates', 'module_type')) {
                $table->string('module_type')->default('general')->after('readable_id'); // general, car_hire, chauffeur
            }
            if (!Schema::hasColumn('booking_estimates', 'car_id')) {
                $table->unsignedBigInteger('car_id')->nullable()->after('zone_id');
            }
            if (!Schema::hasColumn('booking_estimates', 'car_booking_id')) {
                $table->unsignedBigInteger('car_booking_id')->nullable()->after('booking_id');
            }
            if (!Schema::hasColumn('booking_estimates', 'start_date')) {
                $table->date('start_date')->nullable()->after('service_schedule');
            }
            if (!Schema::hasColumn('booking_estimates', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('booking_estimates', 'pickup_time')) {
                $table->string('pickup_time', 20)->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('booking_estimates', 'drop_time')) {
                $table->string('drop_time', 20)->nullable()->after('pickup_time');
            }
            if (!Schema::hasColumn('booking_estimates', 'pickup_type')) {
                $table->string('pickup_type', 30)->nullable()->after('drop_time'); // self, delivery, chauffeur
            }
            if (!Schema::hasColumn('booking_estimates', 'pickup_location')) {
                $table->text('pickup_location')->nullable()->after('pickup_type');
            }
            if (!Schema::hasColumn('booking_estimates', 'drop_location')) {
                $table->text('drop_location')->nullable()->after('pickup_location');
            }
            if (!Schema::hasColumn('booking_estimates', 'pickup_coordinates')) {
                $table->json('pickup_coordinates')->nullable()->after('drop_location');
            }
            if (!Schema::hasColumn('booking_estimates', 'drop_coordinates')) {
                $table->json('drop_coordinates')->nullable()->after('pickup_coordinates');
            }
            if (!Schema::hasColumn('booking_estimates', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('drop_coordinates');
            }
            if (!Schema::hasColumn('booking_estimates', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 8)->nullable()->after('delivery_address');
            }
            if (!Schema::hasColumn('booking_estimates', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            }
        });

        // Make service_id nullable for car hire quotes
        try {
            Schema::table('booking_estimates', function (Blueprint $table) {
                $table->uuid('service_id')->nullable()->change();
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE booking_estimates MODIFY service_id CHAR(36) NULL;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_estimates', function (Blueprint $table) {
            $table->dropColumn([
                'module_type',
                'car_id',
                'car_booking_id',
                'start_date',
                'end_date',
                'pickup_time',
                'drop_time',
                'pickup_type',
                'pickup_location',
                'drop_location',
                'pickup_coordinates',
                'drop_coordinates',
                'delivery_address',
                'delivery_latitude',
                'delivery_longitude'
            ]);
        });
    }
}
