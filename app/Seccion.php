<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seccion extends Model
{
	 use SoftDeletes;
    protected $table = 'seccion';
    protected $dates = ['deleted_at'];
    
    public function anio()
	{
		return $this->belongsTo('App\Anio', 'anio_id');
	}

    public function grado()
	{
		return $this->belongsTo('App\Grado', 'grado_id');
	}

    public function especialidad()
	{
		return $this->belongsTo('App\Especialidad', 'especialidad_id');
	}
}
