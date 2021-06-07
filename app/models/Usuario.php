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
    public $timestamps = true;
    const CREATED_AT = 'FechaCreacion';
    const UPDATED_AT = 'FechaUltimaModificacion';
    const DELETED_AT = 'FechaBaja';
    protected $fillable = [
        'SectorId', 'Nombre', 'Apellido', 'Mail',
        'UsuarioAlta', 'Tipousuario', 'EstadoUsuarioId'
    ];

    public function pedidosUsuarios()
    {
        return $this->hasMany(PedidoUsuario::class);
    }
}
