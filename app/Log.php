<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'log';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
}
