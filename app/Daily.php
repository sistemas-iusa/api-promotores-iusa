<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    protected $table = 'daily';

    protected $fillable = [ 
    'start', 
    'end', 
    'name',
    'content',
    'class',
    'color',
    'cliente',
    'nombre_cliente',
    'lat_cliente',
    'lon_cliente',
    'tipo_cita',
    'descripcion_detalle',
    'fecha',
    'hora_inicio',
    'hora_fin',
    'forma_contacto',
    'objetivo_contacto',
    'checkin',
    'fecha_chekin',
    'hora_checkin',
    'lat_checkin',
    'lng_checkin',
    'checkout',
    'fecha_checkout',
    'hora_checkout',
    'lat_checkout',
    'lng_checkout',
    'finalizo',
    'observacion_final',
    'cancelado',
    'motivo_cancelacion',
    'id_promotor'
    ];
}
