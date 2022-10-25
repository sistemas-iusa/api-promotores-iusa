<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ruta_id')->nullable();
            $table->string('nombre')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('estrato')->nullable();
            $table->string('direccion')->nullable();
            $table->string('codigo_postal')->nullable();
            $table->string('clave_entidad')->nullable();
            $table->string('entidad')->nullable();
            $table->string('clave_municipio')->nullable();
            $table->string('municipio')->nullable();
            $table->string('clave_localidad')->nullable();
            $table->string('localidad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo_electronico')->nullable();
            $table->string('sitio_internet')->nullable();
            $table->string('latitud')->nullable();
            $table->string('longitud')->nullable();
            $table->string('ageb')->nullable();
            $table->string('manzana')->nullable();
            $table->string('numero_ruta')->nullable();
            $table->string('orden_ruta')->nullable();
            $table->string('bandera_encuesta')->nullable();
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
        Schema::dropIfExists('prospects');
    }
}
