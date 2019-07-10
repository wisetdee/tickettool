<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;

    // public function tickets(){
    //     return $this->hasMany('App\Ticket');
    // }
}
