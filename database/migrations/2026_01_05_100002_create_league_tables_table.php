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
        Schema::create('league_tables', function (Blueprint $table) {
            $table->id();

            // League Info
            $table->unsignedBigInteger('league_id');
            $table->string('league_name');
            $table->string('league_country')->nullable();
            $table->string('league_logo')->nullable();
            $table->integer('season');

            // Team Info
            $table->unsignedBigInteger('team_id');
            $table->string('team_name');
            $table->string('team_logo')->nullable();

            // Standing
            $table->integer('rank');
            $table->integer('points');
            $table->integer('goals_diff');
            $table->string('group')->nullable();
            $table->string('form')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();

            // Stats - All
            $table->integer('played')->default(0);
            $table->integer('win')->default(0);
            $table->integer('draw')->default(0);
            $table->integer('lose')->default(0);
            $table->integer('goals_for')->default(0);
            $table->integer('goals_against')->default(0);

            // Stats - Home
            $table->integer('home_played')->default(0);
            $table->integer('home_win')->default(0);
            $table->integer('home_draw')->default(0);
            $table->integer('home_lose')->default(0);
            $table->integer('home_goals_for')->default(0);
            $table->integer('home_goals_against')->default(0);

            // Stats - Away
            $table->integer('away_played')->default(0);
            $table->integer('away_win')->default(0);
            $table->integer('away_draw')->default(0);
            $table->integer('away_lose')->default(0);
            $table->integer('away_goals_for')->default(0);
            $table->integer('away_goals_against')->default(0);

            $table->dateTime('last_updated')->nullable();
            $table->timestamps();

            $table->unique(['league_id', 'season', 'team_id']);
            $table->index('team_id');
            $table->index('rank');
            $table->index(['league_id', 'season']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_tables');
    }
};
