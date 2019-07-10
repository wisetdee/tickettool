<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webklex\IMAP\Client;
use App\Http\Controllers\TicketController;
use App\Ticket;
use App\Attachment;
use App\User;
use App\Config;
use App\Sla;
use App\Log;
use App\Sla_detail;
use App\Failure_class;
use App\Status;
use App\Post;
use App\Notifications\TicketNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ImapController extends Controller
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
    
    public function index() {   //for TEST to be removed
        $this->fetch_mail();
    }

    /**
     * Display a listing of the resource.
     * 
     * @param $server_path for call by scheduler
     * @return \Illuminate\Http\Response
     */
    public function fetch_mail ($server_path = '') 
    {
        $oClient = \Webklex\IMAP\Facades\Client::account('default');
        //Connect to the IMAP Server
        $oClient->connect();
        //Get INBOX
        /** @var \Webklex\IMAP\Support\FolderCollection $inbox */
        $inbox = $oClient->getFolders()[0];

        //Get all Messages of INBOX
        /** @var \Webklex\IMAP\Support\MessageCollection $mails */
        $mails = $inbox->messages()->all()->get();
        
        $ticket_count = 0;
        foreach($mails as $mail){
            try {
                /** @var \Webklex\IMAP\Message $mail */
                $mail_nr = $mails->count() - $ticket_count;
                $this->create_ticket_from_mail($mail, $mail_nr, $server_path);
            } catch (Exception $ex) {   // TODO : catch does not work !!! 
                $log = new Log();
                $log->msg = $ex->getMessage();
                $log->type = 'error_fetch_mail';
                $log->save();
                // TODO
                // $log->notify_admin();
                $ticket_count++;
                continue;
            }
            $ticket_count++;
        }
                
        if(auth()->user() != null) {
            if ($ticket_count==0) {
                return redirect('/tickets')->with('error', 'There is no new ticket in mailbox.');       // fetch_mail() is called by
            } else {
                return redirect('/tickets')->with('success',$ticket_count.' new ticket(s) created');    // fetch_mail() is called by Task Scheduler
            }
        }
    }

    private function create_ticket_from_mail($mail, $mail_nr, $server_path){
        $ticket = null;
        $ticket_nr_exists = $this->get_ticket_nr($mail);
        if($ticket_nr_exists) { 
            // Save mail as a new post into the ticket
            $ticket = Ticket::find($ticket_nr_exists);
            $sender = json_decode(json_encode($mail->getSender())   , true)[0]['personal'];
            $sender_mail = json_decode(json_encode($mail->getSender())   , true)[0]['mail'];
            app('App\Http\Controllers\PostsController')->add_post( $mail->getTextBody(), $sender, $sender_mail , $ticket->id );
            
            // insert attachments
            app('App\Http\Controllers\AttachmentsController')->save_mail_as_attachment($mail_nr , $ticket, $sender, $sender_mail);
            $this->insert_attachments($mail, $ticket, $server_path, $sender_mail);
            if(Config::first()->has_spam_filter & $ticket->is_spam($sender_mail)){
                $mail->moveToFolder('Spam');  
                $mail->delete();  
                return;
            }
            //Move the current Message to 'INBOX.read'
            $mail->moveToFolder('INBOX.read');  
            $mail->delete();  
            return;
        } 
        // insert ticket
        $ticket = new Ticket();
        // $ticket->subject = $mail->getSubject();
        $ticket->subject = Ticket::validate_filename($mail->getSubject() , $is_ticket_subject = true);
        $ticket->content = $mail->getTextBody();    
        // $ticket->content = $mail->getHTMLBody(true);   //shows nothing
        $spoc_user_collection = User::where('is_spoc','1')->take(1)->get(); //get only 1 spoc to assign the ticket to
        $spoc_user = $spoc_user_collection->first();
        if(is_null($spoc_user)) {
            $ticket->user_id = 1;    // = ticket is assigned to nobody , user_id = 1 = app
        } else {
            $ticket->user_id = $spoc_user->id;
        }
        
        $ticket->owner      = json_decode(json_encode($mail->getSender())   , true)[0]['personal'];
        $ticket->owner_mail = json_decode(json_encode($mail->getSender())   , true)[0]['mail'];
        $ticket->status_id  = Status::where('name','NEW')->first()->id;
        if(Config::first()->has_spam_filter & $ticket->is_spam($ticket->owner_mail)){
            $mail->moveToFolder('Spam');  
            $mail->delete();  
            return;
        }

        try {   
            /** @var \Webklex\IMAP\Message $mail */
            $to = $this->get_cc($mail->getTo());
            $cc = $this->get_cc($mail->getCc() , $to);
            $cc = array_merge($cc, $to);
            $ticket->cc = !empty($cc) ? implode(',',$cc) : null;
            $fault_email = null;
            $ticket->validate_email($request = null , $fault_email , $ticket);
        } catch (Exception $ex) { // catch exception does not work
            $log = new Log();
            $log->msg = $ex->getMessage();
            $log->type = 'error_fetch_mail';
            $log->save();
            // TODO
            // $log->notify_admin();
        }
        
        $ticket->save(); 
        $ticket->reacted_at = $ticket->created_at;
        $ticket->save();    
        
        if($ticket->get_domain() === Sla::find('IBM')->domain) {
            $ticket->sla = 'IBM';
            $ticket->save();
            $sla_detail = new Sla_detail();
            $sla_detail->sla_id = $ticket->sla;
            $sla_detail->ticket_id = $ticket->id;
            $sla_detail->failure_class_id = Failure_class::where('id',1)->first()->id;
            $sla_detail->save();
        }
        app('App\Http\Controllers\TicketsController')->send_notification($ticket, $is_fetch_mail = true);
        app('App\Http\Controllers\TicketsController')->send_notification_for_new_ticket_to_customer($ticket);
        
        // insert attachments
        app('App\Http\Controllers\AttachmentsController')->save_mail_as_attachment($mail_nr , $ticket, $ticket->owner, $ticket->owner_mail);
        $this->insert_attachments($mail, $ticket, $server_path, $sender = null);
        
        // add post customer when customer has be notified for new ticket
        $post = new Post();
        $post->is_sent_to_customer = true;
        $post->ticket_id = $ticket->id;
        $post->is_user_action = 0;
        $post->user_id = 1;
        $post->cover_image = 'noimage.jpg';
        $post->title = $ticket->owner .'<'. $ticket->owner_mail .'>'.' has created NEW Ticket #'. $ticket->id;
        $post->body =
        "Sehr geehrter Kunde

        Das Ticket wurde vom Bouygues Service Desk angenommen und wird umgehend bearbeitet.

        Für die Aufrechterhaltung der Kommunikation, bitten wir Sie den Betreff der E-Mails nicht zu ändern.

        Vielen Dank

        ICT Services
        T. +41 44 332 87 00\r\n 
        E. support@ict-servicedesk.ch\r\n";
        $post->save();
        
        //Move the current Message to 'INBOX.read'
        $mail->moveToFolder('INBOX.read');   
        $mail->delete();  
    }

    private function get_ticket_nr($mail) {
        $subjects = explode(" ",$mail->getSubject());
        foreach ($subjects as $sub_text) {
            if (substr($sub_text, 0, 1) === '#') { 
                $ticket_nr = substr($sub_text, 1);
                if(is_numeric($ticket_nr)) {
                    if(Ticket::find($ticket_nr) != null) {
                        return $ticket_nr;
                    } 
                }
            }
        }
        return false;
    }
    
    private function insert_attachments($mail, $ticket, $server_path, $sender = null , $sender_mail = null) {
        if ($mail->hasAttachments()){
            $attachments = $mail->getAttachments();
            foreach($attachments as $attachment){
                // $filename = $attachment->getName();
                $filename = str_replace(" ", "_", $attachment->getName());
                $content  = $attachment->getContent();
                $uid_filename = date("Ymd_His",time()).'_'.$filename; //alternative for human readable timestamp
                // Save attachment to storage
                if ($server_path == '') {   //user push button to fetch mail
                    $attachment->save('storage/attachments',$uid_filename);  //does not work for task scheduler
                } else {    // scheduler calls this methode
                    $fullpath_and_filename = $server_path.'/'.env('APP_NAME').'/'.$_SERVER['DOCUMENT_ROOT'].'storage/app/public/attachments/'.$uid_filename;
                    file_put_contents($fullpath_and_filename , $attachment->getContent());            
                }
                // save attachment to db
                $model_attachment = new Attachment();
                $model_attachment->filename = $uid_filename;
                $model_attachment->ticket_id = $ticket->id;
                if(is_null($sender)) {
                    $model_attachment->owner = $model_attachment->get_customer_company($ticket);  // new ticket
                } else {    
                    $model_attachment->owner = $model_attachment->get_customer_company($ticket, $sender_mail, $sender);         // ticket exists
                }
                $model_attachment->save();
            }
        } 
    }    
    
    /**
     * get Email adresses from email.
     *
     * @return $cc array of email adresses
     * @var \Webklex\IMAP\Message $email
     */
    private function get_cc($email_addr_array_stdobject, $email_addrs=[]) {
        $cc = [];
        $user_emails = User::pluck('email')->toArray();
        foreach ($email_addr_array_stdobject as $to) {
            $email_addr = json_decode(json_encode($to), true)['mail'];
            if ( in_array($email_addr, $cc) == false 
                    && $email_addr != $_ENV['IMAP_USERNAME']        //IMAP_USERNAME = MAIL_HOST , so do not cc to the mail server itself
                    && is_null( $email_addr) == false 
                    && empty(   $email_addr) == false
                    && in_array($email_addr , $user_emails) == false    //do not cc to the db users because they will be notified anyway 
                    && in_array($email_addr , $email_addrs) == false ) { //do not add same email_addr
                array_push ($cc, $email_addr);
            }
        } 
        return $cc;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
