<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPRO extends Model
{
    protected $connection = 'portal_pro';
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
