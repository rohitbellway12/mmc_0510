<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribed_services', function (Blueprint $table) {
            $table->json('service_capabilities')->nullable()->after('is_subscribed');
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
            $table->dropColumn('service_capabilities');
        });
    }
};
