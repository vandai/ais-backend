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
        Schema::table('match_results', function (Blueprint $table) {
            $table->json('events')->nullable()->after('arsenal_result');
            $table->json('lineups')->nullable()->after('events');
            $table->json('statistics')->nullable()->after('lineups');
            $table->boolean('details_fetched')->default(false)->after('statistics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_results', function (Blueprint $table) {
            $table->dropColumn(['events', 'lineups', 'statistics', 'details_fetched']);
        });
    }
};
