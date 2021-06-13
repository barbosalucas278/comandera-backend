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
        'producto_id',
        'Cantidad',
        'Importe',
        'HorarioCreacion',
        'HorarioInicio',
        'HorarioEstipulado',
        'HorarioDeEntrega',
        'NombreCliente',
        'Foto',
        'Eliminado'
    ];
    protected $dateFormat = 'Y-m-d';
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
