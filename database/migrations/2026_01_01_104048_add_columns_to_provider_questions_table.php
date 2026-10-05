<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProviderQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_questions', 'category_id')) {
                $table->foreignUuid('category_id')->nullable()->after('provider_id');
            }
            if (!Schema::hasColumn('provider_questions', 'options')) {
                $table->text('options')->nullable()->after('question_text');
            }
            // Change enum to string to support more types easily
            $table->string('question_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_questions', function (Blueprint $table) {
            $table->enum('question_type', ['yes_no', 'text'])->change();
            if (Schema::hasColumn('provider_questions', 'options')) {
                $table->dropColumn('options');
            }
            if (Schema::hasColumn('provider_questions', 'category_id')) {
                $table->dropColumn('category_id');
            }
        });
    }
}
