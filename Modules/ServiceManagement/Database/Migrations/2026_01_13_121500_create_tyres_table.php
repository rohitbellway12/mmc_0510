<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyres', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('provider_id');
            $table->uuid('category_id')->nullable();
            $table->string('brand');
            $table->string('model')->nullable();
            $table->string('size');
            $table->decimal('price', 24, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->text('images')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tyres');
    }
}
