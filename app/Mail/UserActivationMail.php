<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($title, $link)
    {
        $this->subject($title);
        $this->title = $title;
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@ict-servicedesk.ch')
            ->view('mail.activation');
    }
}
