<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipomaquinaria extends Model
{
	 use SoftDeletes;
    protected $table = 'tipomaquinaria';
    protected $dates = ['deleted_at'];
    
}
