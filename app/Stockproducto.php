<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stockproducto extends Model
{
	 use SoftDeletes;
    protected $table = 'stockproducto';
    protected $dates = ['deleted_at'];
    
    public function producto()
	{
		return $this->belongsTo('App\Producto', 'producto_id');
	}
	
	public function almacen()
	{
		return $this->belongsTo('App\Almacen', 'almacen_id');
	}

}
