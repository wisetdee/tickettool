<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Attachment;
use App\Ticket;
use Illuminate\Http\File;

class AttachmentsController extends Controller
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
        //
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
    public function store(Request $request, $ticket_id)
    {
        // Handle File Upload
        // Get filename with the extension
        $filename = $request->file('attachment')->getClientOriginalName();
        $uid_filename = date("Ymd_His",time()).'_'.$filename;
        // Upload Image
        $result = $request->file('attachment')->storeAS('public/attachments', $uid_filename);
        if($result==false){
            echo 'ATTACHMENT CAN NOT BE STORED';DIE;
        }
        // save attachment to db
        $model_attachment = new Attachment();
        $model_attachment->filename = $uid_filename;
        $model_attachment->ticket_id = $ticket_id;
        $model_attachment->owner = auth()->user()->name;
        $model_attachment->save();
    }

    // See solution : "Need to save a copy of email using imap php and then can be open in outlook express"
    // https://stackoverflow.com/questions/7496266/need-to-save-a-copy-of-email-using-imap-php-and-then-can-be-open-in-outlook-expr 
    // $mbox = imap_open ("{localhost:993/imap/ssl}INBOX", "user_id", "password");
    // $message_count = imap_num_msg($mbox);
    // if ($message_count > 0) {
    //     $headers = imap_fetchheader($mbox, $message_count, FT_PREFETCHTEXT);
    //     $body = imap_body($mbox, $message_count);
    //     file_put_contents('/your/file/here.eml', $headers . "\n" . $body);
    // }
    // imap_close($mbox);
    public static function save_mail_as_attachment($mail_nr , $ticket, $sender, $sender_mail) {
        //get email from mailbox , prepare mail content
        $mbox = imap_open ("{".env('IMAP_HOST')."}INBOX", env('IMAP_USERNAME'), env('IMAP_PASSWORD'));
        $headers = imap_fetchheader($mbox, $mail_nr, FT_PREFETCHTEXT);
        $body = imap_body($mbox, $mail_nr);
        $MC = imap_check($mbox);
        $header = imap_fetch_overview($mbox,"1:{$MC->Nmsgs}",0);
        // $subject = str_replace(env('SELF_MAIL_CODE'),'',$header[0]->subject); does not work will bedeleted
        $subject = Ticket::validate_filename( $header[0]->subject );
        $filename = str_replace(' ' , '_' , $subject .'.eml');
        $uid_filename = date("Ymd_His",time()).'_'.$filename;
        $path_file = 'public/attachments/' . $uid_filename;
        Storage::put($path_file , $headers . "\n" . $body);
        imap_close($mbox);

        // save attachment to db
        $model_attachment = new Attachment();
        $model_attachment->filename  = $uid_filename;
        $model_attachment->ticket_id = $ticket->id;
        $model_attachment->owner     = $model_attachment->get_customer_company($ticket, $sender, $sender_mail);
        $model_attachment->save();
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
        $attachment = Attachment::find($id);
        $ticket_id = $attachment->ticket->id;
        Storage::delete('public/attachments/'.$attachment->filename);
        $attachment->delete();    
        return redirect('\tickets/'.$ticket_id.'/edit')->with('success','Attachment '.$attachment->filename.' has been removed.');
    }
}
