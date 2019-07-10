<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
}
