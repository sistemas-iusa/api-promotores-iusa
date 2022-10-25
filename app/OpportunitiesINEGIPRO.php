<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpportunitiesINEGIPRO extends Model
{
    protected $connection = 'portal_pro';

    protected $table = 'opportunities_inegi';

    protected $fillable = [
        'ruta_id',
        'nombre',
        'razon_social',
        'estrato',
        'direccion',
        'codigo_postal',
        'contacto_nombre',
        'contacto_telefono',
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
        'numero_ruta',
        'orden_ruta',
        'bandera_prospecto',
        'bandera_encuesta',
        'ruta_id',
        'id_promotor'
    ];
}
