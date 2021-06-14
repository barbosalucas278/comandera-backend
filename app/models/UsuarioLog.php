<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioLog extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'UsuarioLog';
    public $incrementing = true;
    public $timestamps = false;
    const DELETED_AT = 'Eliminado';
    protected $fillable = [
        'usuario_id', 'HoraDeIngreso', 'FechaDeIngreso', 'FechaModificacion'
    ];
    protected $dateFormat = 'Y-m-d';
    public function usuarios()
    {
        return $this->hasOne(Usuario::class)->latestOfMany();
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}
