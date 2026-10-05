<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make sub_category_id nullable
        Schema::table('subscribed_services', function (Blueprint $table) {
            $table->uuid('sub_category_id')->nullable()->change();
        });

        // Then update all existing subscribed_services to remove sub_category_id
        // We only need service_id and category_id now
        DB::table('subscribed_services')->update([
            'sub_category_id' => null
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we don't have the original sub_category_id values
        // This is a one-way migration
    }
};
