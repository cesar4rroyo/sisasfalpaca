<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banco extends Model
{
	 use SoftDeletes;
    protected $table = 'banco';
    protected $dates = ['deleted_at'];
    
}
