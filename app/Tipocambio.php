<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tipocambio extends Model
{
	 use SoftDeletes;
    protected $table = 'tipocambio';
    protected $dates = ['deleted_at'];
    
}
