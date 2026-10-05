<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribed_services', function (Blueprint $table) {
            $table->string('estimated_time')->nullable()->after('service_capabilities');
            $table->json('service_types')->nullable()->after('estimated_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribed_services', function (Blueprint $table) {
            $table->dropColumn(['estimated_time', 'service_types']);
        });
    }
};
