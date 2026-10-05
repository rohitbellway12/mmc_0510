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
            $table->string('registration_number')->nullable()->after('model_id');
            $table->boolean('air_conditioning')->default(0)->after('transmission');
            $table->string('service_type')->nullable()->after('air_conditioning'); // full_day, hourly
            $table->time('available_hours_start')->nullable()->after('service_type');
            $table->time('available_hours_end')->nullable()->after('available_hours_start');
            $table->text('preferred_areas')->nullable()->after('available_hours_end');
            $table->string('driving_license')->nullable()->after('images');
            $table->string('vehicle_registration')->nullable()->after('driving_license');
            $table->string('insurance_documents')->nullable()->after('vehicle_registration');
            $table->string('mot_certificate')->nullable()->after('insurance_documents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn([
                'registration_number', 'air_conditioning', 'service_type',
                'available_hours_start', 'available_hours_end', 'preferred_areas',
                'driving_license', 'vehicle_registration', 'insurance_documents', 'mot_certificate'
            ]);
        });
    }
};
