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
        Schema::table('provider_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_questions', 'category_id')) {
                $table->uuid('category_id')->nullable()->after('provider_id');
            }
            if (Schema::hasColumn('provider_questions', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_questions', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->uuid('service_id')->nullable();
        });
    }
};
