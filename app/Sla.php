<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sla extends Model
{
    protected $table = 'sla';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
    
}
