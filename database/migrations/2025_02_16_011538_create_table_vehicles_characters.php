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
        Schema::create('vehicles_characters', function (Blueprint $table) {
            $table->id()->primary();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('character_id');

            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->foreign('character_id')->references('id')->on('characters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('vehicles_characters');
        Schema::enableForeignKeyConstraints();
    }
};
