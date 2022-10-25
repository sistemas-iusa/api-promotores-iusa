<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = 'orders';

    protected $fillable = [
        'folio',
        'idUsuario',
        'nombreUsuario',
        'tipoDistribuidor',
        'idDistribuidor',
        'nombreDistribuidor',
        'orden_compra',
        'estatus',
        'hora',
        'fecha',
        'evidencia',
        'concluciones',
        'id_promotor',
    ];
}
