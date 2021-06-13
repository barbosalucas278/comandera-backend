<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ventas extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'Ventas';
    public $incrementing = true;
    public $timestamps = false;
    const DELETED_AT = 'Eliminado';
    protected $dateFormat = 'Y-m-d';
    protected $fillable = [
        'mesa_id', 'importe', 'fecha', 'Eliminado'
    ];
}
