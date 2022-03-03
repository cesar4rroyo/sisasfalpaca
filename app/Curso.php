<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Curso extends Model
{
	 use SoftDeletes;
    protected $table = 'curso';
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
