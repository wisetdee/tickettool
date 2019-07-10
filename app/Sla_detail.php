<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sla_detail extends Model
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->is_warning_notified = false;
        $this->is_overdue_notified = false;
    }
    

    protected $table = 'sla_detail';
    
    public $primaryKey = 'ticket_id';
    
    public $timestamps = false;

    public function ticket(){
        return $this->belongsTo('App\Ticket');
    }
}
