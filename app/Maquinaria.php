<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maquinaria extends Model
{
	 use SoftDeletes;
    protected $table = 'maquinaria';
    protected $dates = ['deleted_at'];
    
    public function tipomaquinaria()
	{
		return $this->belongsTo('App\Tipomaquinaria', 'tipomaquinaria_id');
	}
    
}
