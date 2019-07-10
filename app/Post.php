<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Post extends Model
{
    // Table Name
    protected $table = 'posts';
    // Primary Key
    public $primaryKey = 'id';
    // Timestamps
    public $timestamps = true;

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function ticket(){
        return $this->belongsTo('App\Ticket');
    }

    public function get_title_for_user_action($ticket_id , $post_user_name){
        return 'Ticket No. #' . $ticket_id . ' - ' . $post_user_name .' has made change to the ticket';
    }

    public function created_at($created_at){
        $created_at !== null ? (new Carbon(strtotime(str_replace('T' ,' ', $created_at).':00')))->timezone(Config::get('app.timezone')) : null;
        return $created_at->format('d.m.Y H:i');                            
    }
        
    public function closed_at($closed_at){
        $closed_at !== null ? (new Carbon(strtotime(str_replace('T' ,' ', $closed_at) .':00')))->timezone(Config::get('app.timezone')) : null;
        return $closed_at->format('d.m.Y H:i');  
    }

    public function get_ticket_customers(){
        $cc = !empty($this->ticket->cc) ? ',' . $this->ticket->cc : '';
        $customers = $this->ticket->owner_mail . $cc;
        return str_replace(',',' , ',$customers);
    }
}
