<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('folio');
            $table->string('idUsuario');
            $table->string('nombreUsuario');
            $table->string('tipoDistribuidor')->nullable();
            $table->string('idDistribuidor')->nullable();
            $table->string('nombreDistribuidor')->nullable();
            $table->string('orden_compra');
            $table->string('estatus');
            $table->string('hora');
            $table->string('fecha');
            $table->string('id_promotor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
