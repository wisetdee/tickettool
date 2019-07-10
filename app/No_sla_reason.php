<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class No_sla_reason extends Model
{
    protected $table = 'no_sla_reason';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;

}
