<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hw extends Model
{
    protected $table = 'hw';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
}
