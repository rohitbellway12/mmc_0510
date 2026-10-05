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
            $table->uuid('provider_id')->nullable()->after('id');
            // Assuming providers table uses UUIDs as well, based on previous context.
            // If providers.id is bigint, use foreignId. Let's check SubscribedService model or Providers table migration if unsure.
            // But previous debug output showed provider_id as "cffcce91-5498..." which is a UUID.
            // Wait, SubscribedService debug output showed "provider_id":"cffcce91-..." which is UUID.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('provider_id');
        });
    }
};
