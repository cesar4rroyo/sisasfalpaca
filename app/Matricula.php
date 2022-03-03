<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matricula extends Model
{
	 use SoftDeletes;
    protected $table = 'matricula';
    protected $dates = ['deleted_at'];
    
    public function anio()
	{
		return $this->belongsTo('App\Anio', 'anio_id');
	}

    public function seccion()
	{
		return $this->belongsTo('App\Seccion', 'seccion_id');
	}

    public function alumno()
	{
		return $this->belongsTo('App\Alumno', 'alumno_id');
	}
}
