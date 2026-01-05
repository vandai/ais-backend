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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('league_id')->unique();
            $table->string('name');
            $table->string('type')->nullable(); // League, Cup
            $table->string('logo')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_flag')->nullable();
            $table->unsignedInteger('season');
            $table->date('season_start')->nullable();
            $table->date('season_end')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->index(['season', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
