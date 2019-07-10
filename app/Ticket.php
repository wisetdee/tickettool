<?php

namespace App;

use App\Sla_detail;
use App\Failure_class;
use App\Hw;
use App\Sw;
use App\No_sla_reason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class Ticket extends Model
{
    protected $table = 'tickets';
    
    public $primaryKey = 'id';
    
    public $timestamps = true;

    public function created_at($request){
        return $request->input('created_at') ? (new Carbon(strtotime(str_replace('T',' ',$request->input('created_at')).':00')))->timezone(Config::get('app.timezone')) : null;
    }
    
    public function reacted_at($request){
        return $request->input('reacted_at') ? (new Carbon(strtotime(str_replace('T',' ',$request->input('reacted_at')).':00')))->timezone(Config::get('app.timezone')) : null;
    }
    
    public function closed_at($request){
        return $request->input('closed_at') ? (new Carbon(strtotime(str_replace('T',' ',$request->input('closed_at')).':00')))->timezone(Config::get('app.timezone')) : null;
    }

    public function attachments(){
        return $this->hasMany('App\Attachment');
    }

    public function posts(){
        return $this->hasMany('App\Post');
    }

    public function status(){
        return $this->belongsTo('App\Status');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function sla(){
        return $this->belongsTo('App\Sla');
    }

    public function sla_detail(){
        return $this->belongsTo('App\Sla_detail');
    }  
    
    public function spent_times(){
        return $this->hasMany('App\Spent_time');
    } 

    public function start_date(){
        if($this->created_at !== null){
            if(!is_a($this->created_at,'Carbon')){
                $this->created_at = Carbon::parse($this->created_at);
            }
        }
        return $this->created_at !== null ? $this->created_at->format('d.m.Y H:i') : '';
    }

    public function reacted_date(){
        if($this->reacted_at !== null){
            if(!is_a($this->reacted_at,'Carbon')){
                $this->reacted_at = Carbon::parse($this->reacted_at);
            }
        }
        return $this->reacted_at !== null ? $this->reacted_at->format('d.m.Y H:i') : '';
    }

    public function closed_date(){
        if($this->closed_at !== null){
            if(!is_a($this->closed_at,'Carbon')){
                $this->closed_at = Carbon::parse($this->closed_at);
            }
        }
        return $this->closed_at !== null ? $this->closed_at->format('d.m.Y H:i') : '';
    }

    public function failure_class_id(){
        if(null == Sla_detail::where('ticket_id' , $this->id)->first()){
            return null;
        } else {
            return Sla_detail::where('ticket_id' , $this->id)->first()->failure_class_id;
        }
    }

    public function hw(){
        if (        null!== Sla_detail::where('ticket_id' , $this->id)->first()){
            $sla_details =  Sla_detail::where('ticket_id' , $this->id)->first();
            if(null !== $sla_details->hw_id) {
                $names = [];
                foreach(explode(',',$sla_details->hw_id) as $id){
                    array_push($names, Hw::find($id)->name);
                }

                return implode(",", $names);
            }
        }
        return null;
    }

    public function sw(){
        if (        null!== Sla_detail::where('ticket_id' , $this->id)->first()){
            $sla_details =  Sla_detail::where('ticket_id' , $this->id)->first();
            if(null !== $sla_details->sw_id) {
                $names = [];
                foreach(explode(',',$sla_details->sw_id) as $id){
                    array_push($names, Sw::find($id)->name);
                }

                return implode(",", $names);
            }
        }
        return null;
    }

    public function no_sla_reason(){
        if (        null!== Sla_detail::where('ticket_id' , $this->id)->first()){
            $sla_details =  Sla_detail::where('ticket_id' , $this->id)->first();
            if(null !== $sla_details->no_sla_reason_id) {
                $names = [];
                foreach(explode(',',$sla_details->no_sla_reason_id) as $id){
                    array_push($names, No_sla_reason::find($id)->name);
                }

                return implode(",", $names);
            }
        }
        return null;
    }

    public static function validate_filename ($str = '' , $is_ticket_subject = false)
    {
        $str = trim($str);                                          // Remove spaces left and right from string
        $str = strip_tags($str);                                    // Strip HTML Tags
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);             // Remove Break/Tabs/Return Carriage
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', '', $str);   // Remove Illegal Chars for folder and filename
        // $str = strtolower($str);                                 // Put the string in lower case
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );     // Remove foreign accents such as Éàû by convert it into html entities and then remove the code and keep the letter.
        $str = htmlentities($str, ENT_QUOTES, "utf-8");             
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        if(!$is_ticket_subject){
            $str = str_replace(' ', '_', $str);                         // Replace Spaces with underlines
        }
        // $str = rawurlencode($str);                                  // Encode special chars that could pass the previous steps and enter in conflict filename on server. ex. "中文百强网"
        $str = str_replace('#', '', $str);      // Remove # , because it make troble in url links
        $str = str_replace('%', '_', $str);     // Replace "%" with underlines to make sure the link of the file will not be rewritten by the browser when querying th file.
        return $str;                                                
    }

    public function get_domain($email_addr = null){
        if(is_null($email_addr)) {
            return substr($this->owner_mail, strpos($this->owner_mail, '@') + 1);
        }else{
            return substr($email_addr, strpos($email_addr, '@') + 1);
        }
    } 

    /**
     * called only by function $ticket->is_spam()
     * when spam filter is enabled and no customer domain found by $ticket->get_domain()
     */
    public function get_domain_asterisk($email_addr){
        $domain = substr($email_addr, strpos($email_addr, '@') + 1);  
        $domain = explode('.',$domain);
        if(count($domain) === 3){
            $domain[0] = '*';
            return implode('.',$domain);
        }else{
            return false;
        }
    } 

    public function get_customer_company_name($email_addr = null) {
        $domain=null;
        if(is_null($email_addr)){
            $domain = $this->get_domain();
        }else{
            $domain = $this->get_domain($email_addr);
        }
        if(null !== Customer::where('domain',$domain)->first()){
            return Customer::where('domain',$domain)->first()->name;
        } else {
            return $domain;
        }
    }

    public function get_urgent_mark_for_sla_solution_overdue() {
        $urgent_mark = ['color' => '', 'text' => ''];

        if ($this->status_id==Status::where('name','NEW')->first()->id) { // NEW Ticket from any other customer, not IBM
            // return ['color' => 'background-color:cyan', 'text' => ''];
            $urgent_mark['color'] = 'background-color:cyan';
        }
        if (!(
                    (isset(Sla_detail::where('ticket_id',$this->id)->first()->no_sla_reason_id) && isset($this->sla)) // = IBM No SLA
                ||  !isset($this->sla)    // = other customer No SLA
            )) {
                $failure_class_id = null;
                $solution_hour = null;
                $warning_hour  = null;
                $due_time  = null;
                $warn_time = null;
                if(isset(Sla_detail::where('ticket_id',$this->id)->first()->failure_class_id)) {
                    $failure_class_id = Sla_detail::where('ticket_id',$this->id)->first()->failure_class_id;
                    $solution_hour = Failure_class::where('id',$failure_class_id)->first()->solution_hour;
                    $warning_hour  = Failure_class::where('id',$failure_class_id)->first()->warning_hour;
                    $due_time  = $this->created_at->addHours($solution_hour);
                    $warn_time = $this->created_at->addHours($warning_hour); 
                }
                $now = Carbon::now()->timezone(Config::get('app.timezone'));
                // dd($warn_time . ' ===== ' . $now . ' ===== ' . $due_time);
                if ($due_time!==null && $warn_time!==null) {
                    if($now->greaterThanOrEqualTo($warn_time)) {
                        if($now->greaterThanOrEqualTo($due_time)) {
                            // $urgent_mark = ['color' => 'background-color:pink' , 'text' => ' overdue since '.$due_time->diffInMinutes($now).' hour(s)'];
                            $urgent_mark = ['color' => 'background-color:pink' , 'text' => ' overdue since '. $this->get_overdue_time($due_time , $now) ];
                        } else {
                            // $urgent_mark = ['color' => 'background-color:wheat', 'text' => ' overdue in '   .$now->diffInMinutes($due_time).' hour(s)'];
                            $urgent_mark = ['color' => 'background-color:wheat' , 'text' => ' overdue in '. $this->get_overdue_time($now , $due_time) ];
                        }
                    }
                }
        }
        return $urgent_mark;
    }

    private function get_overdue_time($time_1 , $time_2) {
        $overdue_time = 0;
        if ( $time_1->diffInHours($time_2) < 1 ) {
            return $time_1->diffInMinutes($time_2) .' minute(s)';
        } else {
            $mins = $time_1->diffInMinutes($time_2);
            return $time_1->diffInHours($time_2) . ' hour(s) ' . $mins%60 .' minute(s)' ;
        }
    }

    public static function get_sla_ticket_ids($tickets){
        $sla_ticket_id = [];
        foreach($tickets as $ticket ) {
            if( 
                (
                       !isset(Sla_detail::where('ticket_id',$ticket->id)->first()->no_sla_reason_id)// no_sla_reason not exists  
                    && !isset(Sla_detail::where('ticket_id',$ticket->id)->first()->hw_id)           // hw failure not exists
                    && !isset(Sla_detail::where('ticket_id',$ticket->id)->first()->sw_id)           // sw failure not exists
                    && isset($ticket->sla)  // is IBM 
                )
                || isset(Sla_detail::where('ticket_id',$ticket->id)->first()->hw_id) // has aSecure hw failure
                || isset(Sla_detail::where('ticket_id',$ticket->id)->first()->sw_id) // has aSecure sw failure
                ) {
                array_push($sla_ticket_id, $ticket->id);
            }
        }
        return $sla_ticket_id;
    }

    public function validate_email($request = null , &$fault_email = null , &$ticket = null) {
        $has_email_fault = false;
        $emails = $this->get_all_customer_emails($request);
        if(empty($emails)){
            return $has_email_fault = true;
        }
        foreach($emails as $email){
            $email = trim($email);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($email) || is_null($email)) {
                $fault_email = $email;
                $has_email_fault= true;
                if (($key = array_search($email, $emails)) !== false) { 
                    unset($emails[$key]); 
                }
                if($ticket !== null) { // this function is called by ImapController->fetch_mail()
                    $ticket->cc = implode (", ", $emails);
                } else {
                    break;
                }
            }
        }
        return $has_email_fault;
    }

    public function get_all_customer_emails($request){
        $emails = [];
        if(null == $request){
            $emails = explode( ',', $this->cc );
            array_push($emails , $this->owner_mail);
            return $emails;
        }else{
            $cc = trim($request->input('cc_mail'));
            if(!empty($cc)) {
                $emails = explode( ',', $cc );
            }
            $owner_mail = trim($request->input('owner_mail'));
            if(!empty($owner_mail)) {
                array_push($emails , $owner_mail);
            }
            return $emails;
        }
    }

    public function is_spam($email_addr){
        $domain=null;
        if(is_null($email_addr)){
            $domain = $this->get_domain();
        }else{
            $domain = $this->get_domain($email_addr);
        }
        if(null != Customer::where('domain',$domain)->first()){
            return false;
        } else {
            $domain = $this->get_domain_asterisk($email_addr); //e.g. *.ibm.ch
            if($domain !== false){
                if( null != Customer::where('domain',$domain)->get()->first()) {
                    return false;   //email_addr is no spam
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }
}