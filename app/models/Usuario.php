<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Usuario';
    public $incrementing = true;
    public $timestamps = false;
    /*const CREATED_AT = 'FechaCreacion';
    const UPDATED_AT = 'FechaUltimaModificacion';*/
    const DELETED_AT = 'FechaBaja';
    protected $fillable = [
        'SectorId', 'Nombre', 'Apellido', 'Mail',
        'UsuarioAlta', 'Tipousuario', 'EstadoUsuarioId', 'FechaCreacion', 'FechaUltimaModificacion'
    ];
    protected $dateFormat = 'Y-m-d';
    public function pedidosUsuarios()
    {
        return $this->hasMany(PedidoUsuario::class);
    }
}
