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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->unique();
            $table->string('referee')->nullable();
            $table->string('timezone')->default('UTC');
            $table->dateTime('match_date');
            $table->unsignedBigInteger('timestamp');

            // Venue
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();

            // League/Competition
            $table->unsignedBigInteger('league_id');
            $table->string('league_name');
            $table->string('league_country')->nullable();
            $table->string('league_logo')->nullable();
            $table->string('league_round')->nullable();
            $table->integer('season');

            // Home Team
            $table->unsignedBigInteger('home_team_id');
            $table->string('home_team_name');
            $table->string('home_team_logo')->nullable();

            // Away Team
            $table->unsignedBigInteger('away_team_id');
            $table->string('away_team_name');
            $table->string('away_team_logo')->nullable();

            // Status
            $table->string('status_long')->nullable();
            $table->string('status_short')->nullable();
            $table->integer('status_elapsed')->nullable();

            $table->timestamps();

            $table->index('match_date');
            $table->index('league_id');
            $table->index('season');
            $table->index(['home_team_id', 'away_team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
