<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profesor extends Model
{
	 use SoftDeletes;
    protected $table = 'profesor';
    protected $dates = ['deleted_at'];
    
    public function person()
	{
		return $this->belongsTo('App\Person', 'person_id');
	}
}
