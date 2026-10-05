<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecColumnsToTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->string('tyre_type')->nullable()->default('tubeless')->after('model'); // tubeless, tube_type, run_flat
            $table->string('season')->nullable()->default('all_season')->after('tyre_type'); // all_season, summer, winter
            $table->string('vehicle_type')->nullable()->default('passenger_car')->after('season'); // passenger_car, suv_4x4, commercial, motorcycle
            $table->string('width')->nullable()->after('vehicle_type'); // e.g. 205
            $table->string('profile')->nullable()->after('width'); // e.g. 55
            $table->string('rim_size')->nullable()->after('profile'); // e.g. R16
            $table->string('speed_rating')->nullable()->after('rim_size'); // e.g. H, V, W, Y
            $table->string('load_index')->nullable()->after('speed_rating'); // e.g. 91
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tyres', function (Blueprint $table) {
            $table->dropColumn([
                'tyre_type',
                'season',
                'vehicle_type',
                'width',
                'profile',
                'rim_size',
                'speed_rating',
                'load_index'
            ]);
        });
    }
}
