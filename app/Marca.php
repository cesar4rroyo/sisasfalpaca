<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
	 use SoftDeletes;
    protected $table = 'marca';
    protected $dates = ['deleted_at'];
    
}
