<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pago extends Model
{
	 use SoftDeletes;
    protected $table = 'pago';
    protected $dates = ['deleted_at'];
	
	public function banco()
	{
		return $this->belongsTo('App\Banco', 'banco_id');
	}
    
    public function movimiento()
    {
        return $this->belongsTo('App\Movimiento', 'movimiento_id');
    }
}