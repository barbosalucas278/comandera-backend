<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Pedido';
    public $incrementing = true;
    public $timestamps = true;
    const CREATED_AT = 'FechaCreacion';
    const UPDATED_AT = 'FechaModificacion';
    const DELETED_AT = 'Eliminado';

    protected $fillable = [
        'EstadoPedidoId',
        'MesaId',
        'CodigoPedido',
        'Cantidad',
        'ProductoId',
        'Cantidad',
        'Importe',
        'HorarioCreacion',
        'HorarioInicio',
        'TiempoEstipulado',
        'HorarioDeEntrega',
        'NombreCliente',
        'Foto',
        'Eliminado'
    ];
}
