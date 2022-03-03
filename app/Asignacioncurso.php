<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asignacioncurso extends Model
{
	 use SoftDeletes;
    protected $table = 'asignacioncurso';
    protected $dates = ['deleted_at'];
    
    public function profesor()
	{
		return $this->belongsTo('App\Profesor', 'profesor_id');
	}
    public function curso()
	{
		return $this->belongsTo('App\Curso', 'curso_id');
	}
    public function anio()
	{
		return $this->belongsTo('App\Anio', 'anio_id');
	}
    public function seccion()
	{
		return $this->belongsTo('App\Seccion', 'anio_id');
	}
}
