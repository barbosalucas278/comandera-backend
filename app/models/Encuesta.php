<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Encuesta';
    public $incrementing = true;
    public $timestamps = false;
    const DELETED_AT = 'Eliminado';
    protected $dateFormat = 'Y-m-d';
    protected $fillable = [
        'mesa_id', 'Restaurante', 'Mozo', 'Comida', 'Comentario', 'FechaCreacion', 'HorarioCreacion', 'Eliminado'
    ];

    public function mesas()
    {
        return $this->belongsTo(Mesa::class);
    }
}
