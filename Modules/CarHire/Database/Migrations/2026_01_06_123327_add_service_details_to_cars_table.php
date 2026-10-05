<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceDetailsToCarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('service_category')->after('id'); // car_hire or chauffeur
            $table->string('pricing_type')->after('daily_rent'); // both, hourly, or daily
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('daily_rent');
            $table->json('features')->nullable()->after('car_type_id');
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
            $table->dropColumn(['service_category', 'pricing_type', 'hourly_rate', 'features']);
        });
    }
}