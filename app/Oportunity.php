<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oportunity extends Model
{
    protected $table = 'opportunities';

    protected $fillable = [
        'ruta_id',
        'nombre',
        'razon_social',
        'estrato',
        'direccion',
        'codigo_postal',
        'clave_entidad',
        'entidad',
        'clave_municipio',
        'municipio',
        'clave_localidad',
        'localidad',
        'telefono',
        'correo_electronico',
        'sitio_internet',
        'longitud',
        'latitud',
        'ageb',
        'manzana',
        'numero_ruta',
        'orden_ruta',
        'bandera_prospecto',
        'bandera_encuesta',
        'id_promotor'
    ];
}
