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
            $table->string('brand')->nullable()->after('car_type_id');
            $table->string('model')->nullable()->after('brand');
            $table->string('year')->nullable()->after('model');
            $table->string('fuel_type')->nullable()->after('year');
            $table->string('transmission_type')->nullable()->after('fuel_type');
            
            // We can keep the old _id columns for now or drop them if they interfere.
            // Let's drop them to clean up as requested "delete krdo".
            $table->dropColumn(['brand_id', 'model_id', 'year_id', 'fuel_type_id', 'transmission_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model', 'year', 'fuel_type', 'transmission_type']);
            // Re-adding ids would be complex due to types and FKs, usually don't rollback destructive migrations like this without care.
        });
    }
};
