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
        Schema::create('films_characters', function (Blueprint $table) {
            $table->id()->primary();
            $table->unsignedBigInteger('film_id');
            $table->unsignedBigInteger('character_id');

            $table->foreign('film_id')->references('id')->on('films');
            $table->foreign('character_id')->references('id')->on('characters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('films_characters');
        Schema::enableForeignKeyConstraints();
    }
};
