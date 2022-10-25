<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $table = 'order_details';

    protected $fillable = [
        'codigo_material',
        'nombre_material',
        'unidades_confirmadas',
        'gpo4',
        'gpo4_nombre',
        'division',
        'segmento',
        'division_comercial',
        'orden_compra_id',
    ];
}
