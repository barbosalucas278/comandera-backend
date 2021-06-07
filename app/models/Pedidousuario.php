<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoUsuario extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'PedidoUsuario';
    public $incrementing = true;
    public $timestamps = true;
    const CREATED_AT = 'FechaCreacion';
    const UPDATED_AT = 'FechaModificacion';
    const DELETED_AT = 'Eliminado';
    protected $fillable = [
        'Pedido_Id', 'usuario_Id'
    ];

    // public function pedidos()
    // {
    //     return $this->hasMany(Pedido::class);
    // }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
