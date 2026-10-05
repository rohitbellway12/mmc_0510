<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderServicePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_service_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id');
            $table->foreignUuid('service_id');
            $table->foreignUuid('variation_id')->nullable();
            $table->foreignUuid('zone_id');
            $table->decimal('price', 24, 3)->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            // Composite unique constraint to prevent duplicate entries
            $table->unique(['provider_id', 'service_id', 'variation_id', 'zone_id'], 'provider_service_variation_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_service_prices');
    }
}
