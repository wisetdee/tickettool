<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Failure_class extends Model
{
    protected $table = 'failure_class';
    
    public $primaryKey = 'id';
    
    public $timestamps = false;
}
