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
        Schema::create('films_planets', function (Blueprint $table) {
            $table->id()->primary();
            $table->unsignedBigInteger('film_id');
            $table->unsignedBigInteger('planet_id');

            $table->foreign('film_id')->references('id')->on('films');
            $table->foreign('planet_id')->references('id')->on('planets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('films_planets');
        Schema::enableForeignKeyConstraints();
    }
};
