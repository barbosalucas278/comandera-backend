<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EncuestaMesa extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'Id';
    protected $table = 'EncuestaMesa';
    public $incrementing = true;
    public $timestamps = false;
    const DELETED_AT = 'Eliminado';
    protected $dateFormat = 'Y-m-d';
    protected $fillable = [
        'mesa_id', 'encuesta_id', 'Eliminado'
    ];

    public function mesas()
    {
        return $this->belongsTo(Mesa::class);
    }
    public function encuestas()
    {
        return $this->belongsTo(Encuesta::class);
    }
}
