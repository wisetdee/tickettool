<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'location';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
}
