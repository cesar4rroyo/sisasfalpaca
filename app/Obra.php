<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obra extends Model
{
	 use SoftDeletes;
    protected $table = 'obra';
    protected $dates = ['deleted_at'];
    
}
