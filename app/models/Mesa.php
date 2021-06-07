<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Mesa';
    public $incrementing = true;
    public $timestamps = false;
    const DELETED_AT = 'Eliminado';

    protected $fillable = [
        'EstadoMesaId', 'Codigo', 'Eliminado'
    ];
}
