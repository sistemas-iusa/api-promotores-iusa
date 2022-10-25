<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RouteList extends Model
{
    protected $table = 'route_list';

    protected $fillable = [
        'entidad',
        'municipio',
        'numero_ruta',
        'orden_ruta',
        'encuestas_realizadas',
        'estatus',
        'fecha_inicio',
        'hora_inicio',
        'latitud_inicio',
        'longitud_inicio',
        'fecha_final',
        'hora_final',
        'latitud_final',
        'longitud_final',
        'id_promotor'
    ];
}
