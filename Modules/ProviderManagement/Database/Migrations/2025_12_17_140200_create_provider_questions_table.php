<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('provider_id');
            $table->foreignUuid('service_id')->nullable();
            $table->text('question_text');
            $table->enum('question_type', ['yes_no', 'text'])->default('yes_no');
            $table->boolean('is_required')->default(1);
            $table->boolean('is_active')->default(1);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_questions');
    }
}
