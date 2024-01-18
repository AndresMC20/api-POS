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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->date('fechaPedido');
            $table->date('fechaEntrega')->nullable();
            $table->integer('anticipo')->nullable();
            $table->integer('saldo')->nullable();
            $table->integer('montoTotal');
            $table->text('historial')->nullable();
            $table->boolean('estadoPedido')->default(0)->nullable();
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_empresa');

            $table->foreign('id_persona')->references('id_persona')->on('personas');
            $table->foreign('id_empresa')->references('id_empresa')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
