<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, delete all existing records since we can't convert UUIDs to bigints
        DB::table('provider_service_prices')->truncate();

        // Then change the column type
        Schema::table('provider_service_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('variation_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_service_prices', function (Blueprint $table) {
            $table->uuid('variation_id')->nullable()->change();
        });
    }
};
