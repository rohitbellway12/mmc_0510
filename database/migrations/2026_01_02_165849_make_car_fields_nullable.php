<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->change();
            $table->unsignedBigInteger('car_type_id')->nullable()->change();
            $table->string('brand')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->string('year')->nullable()->change();
            $table->string('fuel_type')->nullable()->change();
            $table->string('transmission_type')->nullable()->change();
            $table->string('transmission')->nullable()->change();
            $table->integer('seating_capacity')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            //
        });
    }
};
