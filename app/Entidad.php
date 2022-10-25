<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Entidad extends Model
{
    protected $table = 'entidades_inegi';

    protected $fillable = [
        'clave',
        'nombre',
        'abreviacion',
    ];
}
