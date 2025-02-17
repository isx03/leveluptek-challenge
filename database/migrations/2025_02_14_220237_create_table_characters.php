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
        Schema::create('characters', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->unsignedBigInteger('planet_id')->nullable();
            $table->string("height");
            $table->string("mass");
            $table->string("hair_color");
            $table->string("skin_color");
            $table->string("eye_color");
            $table->string("birth_year");
            $table->string("gender");
            $table->binary('original_json');
            $table->timestamps();

            $table->foreign('planet_id')->references('id')->on('planets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('characters');
        Schema::enableForeignKeyConstraints();
    }
};
