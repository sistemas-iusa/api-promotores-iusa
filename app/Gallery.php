<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'gallery';

    protected $fillable = [
        'foto',        
        'fecha',
        'hora',
        'latitud',
        'longitud',
        'id_oportunidad'
    ];
}
