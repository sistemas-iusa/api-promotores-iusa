<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouteListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_ruta')->nullable();
            $table->string('orden_ruta')->nullable();
            $table->string('encuestas_realizadas')->nullable();      
            $table->string('estatus')->nullable(); 
            $table->string('fecha_inicio')->nullable();
            $table->string('hora_inicio')->nullable(); 
            $table->string('latitud_inicio')->nullable();
            $table->string('longitud_inicio')->nullable();
            $table->string('fecha_final')->nullable();
            $table->string('hora_final')->nullable();
            $table->string('latitud_final')->nullable();
            $table->string('longitud_final')->nullable();       
            $table->string('id_promotor')->nullable();
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
        Schema::dropIfExists('route_list');
    }
}
