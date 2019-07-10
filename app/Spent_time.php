<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Spent_time extends Model
{
    protected $table = 'spent_times';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;
    
    public function user(){
        return $this->belongsTo('App\User');
    }

    public function ticket(){
        return $this->belongsTo('App\Ticket');
    }
}
