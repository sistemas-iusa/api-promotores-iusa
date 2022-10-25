<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDaily extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('name')->nullable();
            $table->string('content')->nullable();
            $table->string('color')->nullable();
            $table->string('class')->nullable();
            $table->string('cliente')->nullable();
            $table->string('nombre_cliente')->nullable();
            $table->string('lat_cliente')->nullable();
            $table->string('lon_cliente')->nullable();
            $table->string('email_cliente')->nullable();
            $table->string('tipo_cita')->nullable();
            $table->string('forma_contacto')->nullable();
            $table->string('objetivo_contacto')->nullable();
            $table->string('descripcion_detalle')->nullable();
            $table->string('fecha')->nullable();
            $table->string('hora_inicio')->nullable();
            $table->string('hora_fin')->nullable();
            $table->string('checkin')->nullable();
            $table->string('fecha_checkin')->nullable();
            $table->string('hora_checkin')->nullable();
            $table->string('lat_checkin')->nullable();
            $table->string('lng_checkin')->nullable();
            $table->string('checkout')->nullable();
            $table->string('fecha_checkout')->nullable();
            $table->string('hora_checkout')->nullable();
            $table->string('lat_checkout')->nullable();
            $table->string('lng_checkout')->nullable();
            $table->string('finalizo')->nullable();
            $table->string('observacion_final')->nullable();
            $table->string('cancelado')->nullable();
            $table->string('motivo_cancelacion')->nullable();
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
        Schema::dropIfExists('daily');
    }
}
