<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    protected $table = 'municipios_inegi';

    protected $fillable = [
        'clave_entidad',
        'clave_municipio',
        'nombre'
    ];
}
