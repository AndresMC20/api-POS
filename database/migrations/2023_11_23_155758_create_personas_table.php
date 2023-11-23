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
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombrePersona', 50);
            $table->string('pApellidoPersona', 50);
            $table->string('sApellidoPersona', 50)->nullable();
            $table->string('celularPersona');
            $table->string('direccionPersona', 255)->nullable();
            $table->boolean('estadoPersona')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
