<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->uuid('category_id')->constrained('categories')->onDelete('cascade'); // Car Hire category
            $table->foreignId('car_type_id')->constrained('car_types')->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('fuel_type');
            $table->string('transmission');
            $table->integer('seating_capacity');
            $table->decimal('daily_rent', 10, 2);
            $table->text('description')->nullable();
            $table->json('images')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
