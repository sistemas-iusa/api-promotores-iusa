<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    //
    protected $table = 'distributor';

    protected $fillable = [
        'nombre',
        'idIusa',
        'tipo',
        'direccion',
        'telefono',
        'correo',
        'cp',
        'calificacion',
        'id_oportunidad'
    ];
}
