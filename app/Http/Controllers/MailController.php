<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;

class MailController extends Controller
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
    
    public function send($receiver, $details , Mailer $mailer){
        $mailer
            ->to($request->input('mail'))
            ->send(new \App\Mail\MyMail($request->input('title')));
        $title = "Email is sent!";
        return view('pages.index')->with('title', $title);
    }
}