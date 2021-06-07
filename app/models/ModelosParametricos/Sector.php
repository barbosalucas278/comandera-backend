<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $table = 'Sector';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'Detalle'
    ];
}
