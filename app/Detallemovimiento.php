<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Detallemovimiento extends Model
{
	 use SoftDeletes;
    protected $table = 'detallemovimiento';
    protected $dates = ['deleted_at'];
    
    public function producto()
	{
		return $this->belongsTo('App\Producto', 'producto_id');
	}

    public function movimiento()
	{
		return $this->belongsTo('App\Movimiento', 'movimiento_id');
	}
}
