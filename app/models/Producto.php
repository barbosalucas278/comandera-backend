<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Producto';
    public $incrementing = true;
    public $timestamps = false;
    /*const CREATED_AT = 'FechaCreacion';
    const UPDATED_AT = 'FechaUltimaModificacion';*/
    const DELETED_AT = 'Eliminado';
    protected $fillable = [
        'Codigo', 'TipoProductoId', 'Nombre', 'Stock',
        'Precio', 'FechaCreacion', 'FechaUltimaModificacion'
    ];
    protected $dateFormat = 'Y-m-d';

    public function pedido()
    {
        return $this->hasMay(Pedido::class, 'producto_id');
    }
}
