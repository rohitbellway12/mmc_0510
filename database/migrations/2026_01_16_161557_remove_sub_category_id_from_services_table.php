<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Keep the column, but set all values to null
        \Illuminate\Support\Facades\DB::table('services')->update(['sub_category_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore lost data
    }
};
