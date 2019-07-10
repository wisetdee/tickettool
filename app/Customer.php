<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    
    public $primaryKey = 'id';
    
    public $timestamps = false;
}
