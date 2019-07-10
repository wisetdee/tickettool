<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachments';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;

    public function ticket(){
        return $this->belongsTo('App\Ticket');
    }

    public function get_customer_company($ticket, $sender = null, $email_addr = null){
        $company = $ticket->get_customer_company_name($email_addr);
        if(is_null($sender)){
            return $company . ' - ' . $ticket->owner;
        }else{
            return $company . ' - ' . $sender;
        }
    }
}
