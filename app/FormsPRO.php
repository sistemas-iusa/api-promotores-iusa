<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormsPRO extends Model
{
    protected $connection = 'portal_pro';
    protected $table = 'forms';

    protected $fillable = [
        'pregunta1',
        'pregunta2',
        'pregunta3',
        'pregunta4',
        'pregunta5',
        'pregunta6',
        'pregunta7',
        'pregunta8',
        'pregunta9',
        'pregunta10',
        'pregunta11',
        'pregunta12',
        'pregunta13',
        'comentario13',
        'pregunta14',
        'motivo_fin_encuesta',
        'fecha',
        'hora',
        'latitud',
        'longitud',
        'id_oportunidad'
    ];
}
