<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Ticket;
use App\Attachment;
use App\User;
use App\Post;
use App\Sla;
use App\Sla_detail;
use App\Customer;
use App\Failure_class;
use App\Status;
use App\Spent_time;
use App\Notifications\TicketNotification;
use App\Rules\IbmSlaValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DataTables;

class TicketsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sla_ticket_filter = null;
        $tickets = Ticket::whereIn('status_id' , Status::where('name','!=','CLOSED')->get('id'))->paginate(10);
        return view('tickets.index')
                    ->with('tickets', $tickets)
                    ->with('title' , 'All OPEN Tickets')
                    ->with('class' , 'success')
                    ->with('text_color' , 'dark');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ticket_config()
    {
        return view('tickets.ticket_config');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_sla()
    {
        $tickets = Ticket::where([
                                    ['sla','!=',null] , 
                                    ['status_id' , '<>' , Status::where('name', 'CLOSED')->first()->id]
                                ])->orderBy('created_at','desc')->get();
        $sla_ticket_ids = Ticket::get_sla_ticket_ids($tickets);
        $tickets = Ticket::whereIn('id',$sla_ticket_ids)->orderBy('created_at','desc')->paginate(10);
        return view('tickets.index')
                    ->with('tickets', $tickets)
                    ->with('title' , 'IBM SLA OPEN Tickets')
                    ->with('class' , 'warning')
                    ->with('text_color' , 'dark');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_sla_closed()
    {
        $tickets = Ticket::where([
                                    ['sla','!=',null] , 
                                    ['status_id' , '=' , Status::where('name', 'CLOSED')->first()->id]
                                ])->orderBy('created_at','desc')->get();
        $sla_ticket_ids = Ticket::get_sla_ticket_ids($tickets);
        $tickets = Ticket::whereIn('id',$sla_ticket_ids)->orderBy('created_at','desc')->paginate(10);
        return view('tickets.index')
                    ->with('tickets', $tickets)
                    ->with('title' , 'IBM SLA CLOSED Tickets')
                    ->with('class' , 'secondary')
                    ->with('text_color' , 'white');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index_closed()
    {
        $tickets = Ticket::where([
                                    ['status_id' , '=' , Status::where('name', 'CLOSED')->first()->id]
                                ])->orderBy('created_at','desc')->paginate(10);
        return view('tickets.index')
                    ->with('tickets', $tickets)
                    ->with('title' , 'All CLOSED Tickets')
                    ->with('class' , 'dark')
                    ->with('text_color' , 'white');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tickets.create')
                    ->with('title' , 'Create New Ticket')
                    ->with('class' , 'info')
                    ->with('text_color' , 'dark');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'subject' => 'required',
            'content' => 'required',
            'owner'   => 'required',
            'owner_mail' => new IbmSlaValidator($request),
        ]);
        // insert ticket
        $ticket = new Ticket();
        $ticket->status_id = 1;
        $ticket->subject = $request->input('subject');
        $ticket->content = $request->input('content');
        $ticket->user_id = $request->input('user_id');
        $ticket->status_id  = $request->input('status_id');
        // $ticket->content = $mail->getHTMLBody(true);   //shows nothing
        $user = User::find(auth()->user()->id);
        $ticket->owner = $user->name;
        $ticket->owner_mail = $request->input('owner_mail') != null ? $request->input('owner_mail') : $user->email;
        $ticket->cc = $request->input('cc');   //todo verify email address
        $ticket->save();
        $ticket->reacted_at = $ticket->created_at;
        $ticket->closed_at = Status::find($request->input('status_id'))->name == 'CLOSED' ? Carbon::now() : null;
        $ticket->save();    
        
        // store attachment
        if($request->hasFile('attachment')){
            app('App\Http\Controllers\AttachmentsController')->store($request , $ticket->id);
        } 
        $this->send_notification($ticket);     
        $this->send_notification_for_new_ticket_to_customer($ticket);
        
        $post = new Post();
        $post->is_sent_to_customer = true;
        $post->ticket_id = $ticket->id;
        $post->is_user_action = 0;
        $post->user_id = 1;
        $post->cover_image = 'noimage.jpg';
        $post->title = 'User : ' . auth()->user()->name . ' has created NEW Ticket #'. $ticket->id;
        $post->body = "Sehr geehrter Kunde

        Das Ticket wurde vom Bouygues Service Desk angenommen und wird umgehend bearbeitet.

        Für die Aufrechterhaltung der Kommunikation, bitten wir Sie den Betreff der E-Mails nicht zu ändern.

        Vielen Dank

        ICT Services
        T. +41 44 332 87 00\r\n 
        E. support@ict-servicedesk.ch\r\n";
        $post->save();


        // insert sla_detail if ticket has SLA
        if($ticket->get_domain() === Sla::find('IBM')->domain) {
            $ticket->sla = 'IBM';
            $ticket->save();    //for IBM Tickets
            $sla_detail = new Sla_detail();
            $sla_detail->sla_id = $ticket->sla;
            $sla_detail->ticket_id = $ticket->id;
            $sla_detail->save();
            return redirect('/tickets/'. $ticket->id.'/edit')->with('success', 'This ticket has IBM SLA. Please, check at least failure class and the sla details. Then click Save !');
        } else {
            // return view('tickets.show')->with('ticket' , $ticket);  // alternative to $this->show($ticket->id);
            return $this->show($ticket->id);
        }  
    }
    
    public function send_notification($ticket, $is_fetch_mail = false)
    {
        $creator = ($is_fetch_mail) ? $ticket->owner . '<' . $ticket->owner_mail . '>' : auth()->user()->name;
        $details = [
            'subject' => 'NEW Ticket No. #'.$ticket->id . ' - ' . $ticket->subject,
            'title' => $creator . ' has created a new ticket', 
            'body' => "\r\n\r\n" . '--------------- Ticket Content ---------------' . "\r\n" . $ticket->content,
            'links' => env('APP_URL').'tickets/'.$ticket->id,
            'order_id' => 101
        ];
        if(!$is_fetch_mail && auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }

    public function send_notification_for_post_deleted($post) {
        $ticket = Ticket::find($post->ticket_id);
        $mark_text_for_user = auth()->user()->name . ' has deleted the post from the ticket #' . $ticket->id . ' - ' . $ticket->subject;
        $post_owner = $post->user_id == 1 ? $post->title : User::find($post->user_id)->name;
        $details = [
            'subject' => 'DELETED Post : "' . $post->title . '" of Ticket No. #'.$ticket->id . ' - ' . $ticket->subject,
            'links' => env('APP_URL').'tickets/'.$ticket->id,
            'title' => auth()->user()->name .' has deleted his post from ticket No. #'.$ticket->id . ' - ' . $ticket->subject , 
            'body' => 'This post was belong to '. $post_owner ."\r\n\r\n" .'--------------- Post Content ---------------' . "\r\n" . $post->body,
            'order_id' => 101
        ];
        if(auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }
    
    public function send_notification_for_post_updated($post, $post_update_text) {
        $ticket = Ticket::find($post->ticket_id);
        $details = [
            'subject' => 'UPDATED Post : "' . $post->title . '" of Ticket No. #'.$ticket->id . ' - ' . $ticket->subject,
            'links' => env('APP_URL').'tickets/'.$ticket->id,
            'title' => auth()->user()->name . ' has changed : ' , 
            'body' => $post_update_text . "\r\n\r\n" .'--------------- Ticket Content ---------------' . "\r\n" . $ticket->content,
            'order_id' => 101
        ];
        if(auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }

    public function send_notification_for_post($sender, $sender_mail, $post, $is_sent_to_customer = false) {
        $ticket = Ticket::find($post->ticket_id);
        $mark_text_for_user = $sender . ' has sent this post to the customer(s) :'.
                                "\r\n" . $ticket->owner_mail .
                                "\r\n" . $ticket->cc . "\r\n\r\n";
        $details = [
            'subject' => 'UPDATE Ticket No. #'.$ticket->id . ' - ' . $ticket->subject,
            'links' => '',
            'title' => $sender .'<'. $sender_mail . '>  has posted in ticket No. #'.$ticket->id . ' - ' . $ticket->subject , 
            'body' => $post->body ."\r\n\r\n" .'--------------- Ticket Content ---------------' . "\r\n" . $ticket->content,
            'order_id' => 101
        ];
        if($is_sent_to_customer) {
            $this->send_mail_to_customer($ticket, $details);
        }
        if($is_sent_to_customer) {
            $details['body'] = $mark_text_for_user . $details['body'];
        }
        $details['links'] = $this->show_ticket_url_links_for_user($ticket);
        if(Auth::check() && auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }
    
    private function show_ticket_url_links_for_user($ticket) {
        return null!==auth()->user() ? env('APP_URL').'tickets/'.$ticket->id : '';
    }

    public function send_notification_for_ticket_changed($sender, $sender_mail, $post) {
        $ticket = Ticket::find($post->ticket_id);
        $title = strpos($post->title, 'has closed Ticket #') ? $post->title : $sender . ' has changed : ';
        $details = [
            'subject' => $post->title,
            'links' => env('APP_URL').'tickets/'.$ticket->id,
            'title' => $title , 
            'body' => $post->body . "\r\n\r\n" .'--------------- Ticket Content ---------------' . "\r\n" . $ticket->content,
            'order_id' => 101
        ];
        if(auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }

    public function send_notification_for_overdue() {
        $tickets = Ticket::all();
        $details_array = [];
        foreach ($tickets as $ticket) {
            $urgent_mark = $ticket->get_urgent_mark_for_sla_solution_overdue($ticket);
            $sla_detail = Sla_detail::find($ticket->id);
            if( $ticket->status->name == 'CLOSED') continue;    //do not need to notify user if ticket is closed
            if( !empty($urgent_mark['text'])) {
                if ( $sla_detail->is_warning_notified && $sla_detail->is_overdue_notified ) {           // ticket is overdue
                    continue;
                }   
                if ( $sla_detail->is_warning_notified && strpos($urgent_mark['text'], 'overdue in')) {  // ticket will be overdue soon
                    continue;
                }
                $overdue_text = null;
                if(strpos($urgent_mark['text'], 'overdue in')){
                    $overdue_text = ' will be ' . $urgent_mark['text'];
                    $sla_detail->is_warning_notified = true;
                }
                if(strpos($urgent_mark['text'], 'overdue since')){
                    $overdue_text = ' has been ' . $urgent_mark['text'];
                    $sla_detail->is_warning_notified = true;
                    $sla_detail->is_overdue_notified = true;
                }
                $sla_detail->save();
                $details = [
                    'subject' => 'WARNING - Ticket #' . $ticket->id . $overdue_text ,
                    'links' => env('APP_URL').'tickets/'.$ticket->id,
                    'title' => 'Please take some action. Because, ticket #' . $ticket->id . ' - "' . $ticket->subject . '"   ' . $overdue_text , 
                    'body' => 'You have following options :'."\r\n"
                                . ' - Solve the problem and close the ticket '."\r\n"
                                . ' - Inform the customer if you need more time to solve (send post to the customer) '."\r\n"
                                . ' - Change ticket to No SLA by choosing at least one No SLA reason '."\r\n\r\n"
                                . "\r\n\r\n" . '--------------- Ticket Content ---------------' . "\r\n" . $ticket->content,
                    'order_id' => 101
                ];
                $this->send_mail_to_users($ticket, $details);
            }
        }
    }

    public function send_notification_for_closed_ticket_to_customer($ticket) {
        $details = [
            'subject' => 'CLOSED Ticket #' . $ticket->id . ' - ' . $ticket->subject,
            'title' => 'Das Problem ist gelöst.' , 
            'body' => "Sehr geehrter Kunde

                        Das Troubleticket #$ticket->id ist erledigt

                        Vielen Dank

                        ICT Services
                        T. +41 44 332 87 00 
                        E. support@ict-servicedesk.ch\r\n\r\n". 

                        '--------------- Ihre Meldung ---------------' . "\r\n" . 

                        $ticket->content,
            'links' => '',
            'order_id' => 101
        ];
        $this->send_mail_to_customer($ticket, $details);
    }

    public function send_notification_for_new_ticket_to_customer($ticket) {
        $details = [
            'subject' => 'NEW Ticket #' . $ticket->id . ' - ' . $ticket->subject,
            'title' => 'Neues Ticket #' . $ticket->id . ' - "' . $ticket->subject . '"  von  ' . $ticket->owner . '<'. $ticket->owner_mail .'>' , 
            'body' => "Sehr geehrter Kunde

                        Das Ticket wurde vom Bouygues Service Desk angenommen und wird umgehend bearbeitet.

                        Für die Aufrechterhaltung der Kommunikation, bitten wir Sie den Betreff der E-Mails nicht zu ändern.

                        Vielen Dank

                        ICT Services
                        T. +41 44 332 87 00\r\n 
                        E. support@ict-servicedesk.ch\r\n\r\n". 

                        '--------------- Ihre Meldung ---------------' . "\r\n" . 

                        $ticket->content,
            'links' => '',
            'order_id' => 101
        ];
        $this->send_mail_to_customer($ticket, $details);
    }

    private function send_mail_to_customer($ticket, $details) {
        // notify customer
        $customer_emails = !empty($ticket->cc) ? explode(',',$ticket->cc) : []; 
        array_push($customer_emails, $ticket->owner_mail);  //send mail to the customer and cc
        foreach($customer_emails as $customer_email) {
            Notification::route('mail', $customer_email)->notify(new TicketNotification($details));    
        }
    }

    private function send_mail_to_users($ticket, $details) {
        // notify users
        $users =  User::where('id','>',1)->get();
        foreach ($users as $user) {
            if($user->is_notify) {
                Notification::send($user, new TicketNotification($details));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = Ticket::find($id);
        $ticket->created_at = isset($ticket->created_at) ? (new Carbon($ticket->created_at))->timezone(Config::get('app.timezone')) : null;  //show.blade uses Carbon for $date->format()
        $ticket->reacted_at = isset($ticket->reacted_at) ? (new Carbon($ticket->reacted_at))->timezone(Config::get('app.timezone')) : null;  //show.blade uses Carbon for $date->format()
        $ticket->closed_at  = isset($ticket->closed_at)  ? (new Carbon($ticket->closed_at))->timezone(Config::get('app.timezone'))  : null;  //show.blade uses Carbon for $date->format()
        return view('tickets.show')->with('ticket' , $ticket);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ticket = Ticket::find($id);
        if(null == $ticket->user_id || 1 == $ticket->user_id) { // user_id = 1 = Nobody
            return view('tickets.edit_user')->with('ticket' , $ticket);
        }
        return view('tickets.edit')->with('ticket' , $ticket);
    }
    
    public function update_user(Request $request)
    {
        $ticket = Ticket::find($request->input('ticket_id'));
        $ticket->user_id = $request->input('user_id');
        $ticket->save();
        $post = new Post();
        $post->ticket_id = $ticket->id;
        $post->title = $post->get_title_for_user_action($post->ticket_id , auth()->user()->name);
        $this->add_user_action_to_post($post , strtoupper('Assignee'), '' , $ticket->user->name);
        app('App\Http\Controllers\PostsController')->add($post);
        return redirect('tickets/'.$ticket->id)->with('success' , 'You have assigned ' . User::find($ticket->user->id)->name . ' to the ticket #' . $ticket->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subject' => 'required',
            'content' => 'required',
            'owner'   => 'required',
            'owner_mail' => new IbmSlaValidator($request), // validate owner_mail , ibm sla ticket data etc.
        ]);
        //prepare for changing ticket data to save in the Ticket History (= post.is_user_action)
        $ticket = Ticket::find($id);
        $old_ticket = $ticket->replicate();
        $old_ticket->id = $ticket->id;
        $old_ticket->created_at = $ticket->created_at;
        $old_ticket->reacted_at = $ticket->reacted_at;
        $old_ticket->closed_at = $ticket->closed_at;
        $old_sla_details = ['failure_class_id'  => $old_ticket->failure_class_id(),
                            'hw'                => $old_ticket->hw(),
                            'sw'                => $old_ticket->sw(),
                            'no_sla_reason'     => $old_ticket->no_sla_reason(),
                            ];

        //get new data from view
        $ticket->subject = $request->input('subject');
        $ticket->content = $request->input('content');
        $search  = array('<pre>', '</pre>');    //pre must be remove, because it is inserted by app on edit
        $replace = array('','');
        $ticket->content    = str_replace($search, $replace, $ticket->content);
        $ticket->status_id  = $request->input('status_id');
        $ticket->user_id    = $request->input('user_id');
        $ticket->owner      = $request->input('owner');
        $ticket->owner_mail = $request->input('owner_mail');
        $ticket->cc         = str_replace(' ','',$request->input('cc_mail'));
        $ticket->created_at = $ticket->created_at($request);
        $ticket->reacted_at = $ticket->reacted_at($request);
        if(null == $ticket->closed_at($request) && $old_ticket->status->name !== 'CLOSED' && $ticket->status->name == 'CLOSED') {
            $ticket->closed_at = Carbon::now();
        } else {
            $ticket->closed_at  = $ticket->closed_at($request);
        }
        if($request->input('sla_yes_no')=='yes') {
            $ticket->sla = 'IBM';
        } else {
            $sla_detail = Sla_detail::where('ticket_id',"=", $ticket->id)->first();
            if(isset($sla_detail)) {
                $sla_detail->delete();
            } 
            $ticket->sla = null;
        }
        if($ticket->sla === 'IBM') {
            $sla_detail = Sla_detail::where('ticket_id',"=", $ticket->id)->first();
            if(!isset($sla_detail) || $sla_detail->count()==0){
                $sla_detail = new Sla_detail();
                $sla_detail->sla_id = $ticket->sla;
            } 
            $sla_detail->location_id = $request->input('location');
            $sla_detail->failure_class_id = $request->input('failure_class');
            $sla_detail->failure_class_id = null !== $sla_detail->failure_class_id ? $sla_detail->failure_class_id : 1;
            $sla_detail->no_sla_reason_id = null;
            $sla_detail->no_sla_reason_id = $request->input('no_sla_reason') ? implode(',',$request->input('no_sla_reason')) : null;
            $sla_detail->hw_id = $request->input('hw') ? implode(',',$request->input('hw')) : null;
            $sla_detail->sw_id = $request->input('sw') ? implode(',',$request->input('sw')) : null;
            $sla_detail->ticket_id = $ticket->id;
            $sla_detail->save();
        } 
        
        $ticket->save();
        $this->save_change_in_log_and_notify_user($old_ticket , $old_sla_details , $ticket);

        if($request->hasFile('attachment')){
            app('App\Http\Controllers\AttachmentsController')->store($request , $ticket->id);
        }                
        return redirect('tickets/'.$ticket->id)->with('success','Ticket '. $ticket->id .' Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Ticket  $old, $new
     * @return \Illuminate\Http\Response
     */
    private function save_change_in_log_and_notify_user($old, $old_sla_details , $new) {
        $change_exists = false;
        $times_changed = false;
        $post = new Post();
        $post->is_user_action = true;
        $post->ticket_id = $new->id;    // $new = old ticket with new data, has id  But, $old is an unsaved replicated ticket of $new, that is why $old has no id
        $post->cover_image = 'noimage.jpg';
        $post->user_id = auth()->user()->id;
        // compare text data of ticket db table
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Subject')           , $old->subject , $new->subject , $has_linebreak = true);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Status')            , $old->status->name , $new->status->name);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Assignee')          , $old->user->name , $new->user->name);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Customer')          , $old->owner , $new->owner);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Customer Email')    , $old->owner_mail , $new->owner_mail);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('CC Email')          , $old->cc , $new->cc , $has_linebreak = true);
        // compare datetime
        $times_changed = $times_changed | $this->add_user_action_to_post($post , strtoupper('Start Date')        , $old->start_date()   , $new->start_date());
        $times_changed = $times_changed | $this->add_user_action_to_post($post , strtoupper('Reaction Date')     , $old->reacted_date() , $new->reacted_date());
        $times_changed = $times_changed | $this->add_user_action_to_post($post , strtoupper('End Date')          , $old->closed_date()  , $new->closed_date());
        $change_exists = $change_exists | $times_changed;
        // compare sla_details
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Failure Class')     , $old_sla_details['failure_class_id'] , $new->failure_class_id());
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Hardware')          , $old_sla_details['hw'] , $new->hw() , $has_linebreak = true);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Software')          , $old_sla_details['sw'] , $new->sw() , $has_linebreak = true);
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('No SLA Reason')     , $old_sla_details['no_sla_reason'] , $new->no_sla_reason() , $has_linebreak = true);
        // Problem description at the buttom of the email, because it could be very long
        $change_exists = $change_exists | $this->add_user_action_to_post($post , strtoupper('Content')           , $old->content , $new->content , $has_linebreak = true);
        if($change_exists){
            $post->title = $post->get_title_for_user_action($new->id , $post->user->name);

            // Reset Overdue Warning, if no_sla_reason changes between NULL and NOT NULL
            $sla_detail = Sla_detail::find($new->id, 'ticket_id');
            if(null != $sla_detail){
                if(
                        ( empty($old_sla_details['no_sla_reason']) && !empty($new->no_sla_reason()))
                    ||  (!empty($old_sla_details['no_sla_reason']) &&  empty($new->no_sla_reason()))
                    ){
                        $sla_detail->is_warning_notified = 0;
                        $sla_detail->is_overdue_notified = 0;
                        $sla_detail->save();
                    }
            }

            // save ticket change log (=post.is_user_action) and notify users
            if($new->status->name === 'CLOSED' && $old->status->name !== 'CLOSED'){
                $this->send_notification_for_closed_ticket_to_customer($new);
                $post = new Post();
                $post->is_sent_to_customer = true;
                $post->ticket_id = $new->id;
                $post->is_user_action = 0;
                $post->user_id = 1;
                $post->cover_image = 'noimage.jpg';
                $post->title = 'User : ' . auth()->user()->name . ' has closed Ticket #'. $new->id;
                $post->body = "Sehr geehrter Kunde

                Das Troubleticket #" . $new->id . " ist erledigt

                Vielen Dank

                ICT Services
                T. +41 44 332 87 00 
                E. support@ict-servicedesk.ch";
                $post->save();
            }
            app('App\Http\Controllers\PostsController')->add($post);
        }
        if ($times_changed && $new->sla !== null) {
            $sla_detail = Sla_detail::find($new->id);
            $sla_detail->is_warning_notified = false;
            $sla_detail->is_overdue_notified = false;
            $sla_detail->save();
        }
    }

    private function add_user_action_to_post($post , $change_topic , $old_text , $new_text , $has_linebreak = false) {
        $line_break = $has_linebreak ? "\r\n":'';
        if( trim($old_text) !== trim($new_text) ) {
            $post->body = $post->body . "\r\n" . $change_topic . " : " . strip_tags(nl2br("\r\n" . ' from '. $line_break . $old_text . $line_break . ' to ' . $line_break . $new_text));
            $post->body = $post->body . "\r\n" . '_________________________________________'."\r\n";
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ticket = Ticket::find($id);
        if(is_null($ticket)){
            return redirect('/tickets')->with('error', 'Ticket #'.$id.' does not exists !!! Maybe other user has already deleted it.');
        }
        foreach ($ticket->attachments as $attachment){
            Storage::delete('public/attachments/'.$attachment->filename);
            $attachment->delete();
        }
        foreach ($ticket->posts as $post){
            Storage::delete('public/cover_images/'.$post->cover_image);
            $post->delete();
        }
        $sla_detail = Sla_detail::where('ticket_id','=',$ticket->id);
        $sla_detail->delete();
        $ticket->delete();            
        $this->send_notification_for_deleted_ticket_to_user($ticket);
        return redirect('/tickets')->with('success','Ticket No.'.$ticket->id.' Removed');
    }
    
    public function send_notification_for_deleted_ticket_to_user($ticket) {
        $details = [
            'subject' => 'DELETED Ticket #' . $ticket->id . ' - ' . $ticket->subject,
            'links'   => '',
            'title'   => auth()->user()->name . ' has deleted ticket #' . $ticket->id , 
            'body'    => 'Ticket owner = ' . $ticket->owner . ' <' . $ticket->owner_mail . '>' 
                        ."\r\n\r\n" . '--------------- Ticket Content ---------------'
                        ."\r\n" . $ticket->content,
            'order_id' => 101
        ];
        if(auth()->user()->not_notify_my_action) {
            return;
        }
        $this->send_mail_to_users($ticket, $details);
    }

    public static function get_sla_yes_no($ticket) {
        $sla_detail = null;
        $sla_detail = null;
        if (null === Sla_detail::where('ticket_id',$ticket->id)->first()) {
            return 'NO';
        } else {
            $sla_detail = Sla_detail::where('ticket_id',$ticket->id)->first();
        }
        if (   !isset($sla_detail->no_sla_reason_id)// has no_sla_reason
            && !isset($sla_detail->hw_id)           // has no controller hardware failure
            && !isset($sla_detail->sw_id)           // has no controller software failure
            && isset($ticket->sla)) { // IBM Ticket
            return 'UNCLEAR';
        }
        if (
                    (isset(Sla_detail::where('ticket_id',$ticket->id)->first()->no_sla_reason_id) && isset($ticket->sla)) // = IBM No SLA
                ||  !isset($ticket->sla)    // = other customer No SLA
            ) {
            return 'NO';
        } else {
            return 'YES'; 
        }  
    }

    public static function get_failure_class($ticket) {
        if (isset(Sla_detail::where('ticket_id',$ticket->id)->first()->failure_class_id)) {
            return Sla_detail::where('ticket_id',$ticket->id)->first()->failure_class_id;
        } else {
            return null;
        }
    }
    
    public static function get_spent_time($ticket) {
        return DB::table('spent_times')->where('ticket_id','=',$ticket->id)->sum('hour');
    }
}
