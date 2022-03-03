<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Concepto extends Model
{
	 use SoftDeletes;
    protected $table = 'concepto';
    protected $dates = ['deleted_at'];
    
}
