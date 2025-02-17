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
        Schema::create('planets', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('rotation_period');
            $table->string('orbital_period');
            $table->string('diameter');
            $table->string('climate');
            $table->string('gravity');
            $table->string('terrain');
            $table->string('surface_water');
            $table->string('population');
            $table->binary('original_json');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planets');
    }
};
