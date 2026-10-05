<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingEstimatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('booking_estimates')) {
            Schema::create('booking_estimates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('readable_id')->nullable()->index();
                $table->foreignUuid('provider_id')->index();
                $table->foreignUuid('customer_id')->nullable()->index();
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_email')->nullable();
                $table->text('customer_address')->nullable();
                $table->foreignUuid('service_id')->index();
                $table->foreignUuid('category_id')->nullable()->index();
                $table->foreignUuid('sub_category_id')->nullable()->index();
                $table->foreignUuid('zone_id')->nullable()->index();
                $table->string('car_model')->nullable();
                $table->string('car_registration_number')->nullable();
                $table->string('car_image')->nullable();
                $table->text('damage_description')->nullable();
                $table->dateTime('service_schedule')->nullable();
                $table->string('service_type')->default('fixed_price'); // fixed_price, quotation_based
                $table->decimal('price', 24, 2)->default(0);
                $table->decimal('tax_amount', 24, 2)->default(0);
                $table->decimal('discount_amount', 24, 2)->default(0);
                $table->decimal('total_amount', 24, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('pending'); // pending, accepted, rejected, expired, converted_to_booking
                $table->foreignUuid('booking_id')->nullable()->index();
                $table->string('link_token', 64)->unique();
                $table->dateTime('expired_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_estimates');
    }
}
