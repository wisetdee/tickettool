<?php
namespace App\Notifications;
   
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
   
class TicketNotification extends Notification
{
    use Queueable;
  
    private $details;
   
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }
   
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }
   
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->details['subject'])
                    ->line($this->details['links'])
                    ->greeting($this->details['title'])
                    ->line(nl2br($this->details['body']))  
                    // need nl2br() 
                    // and in public\vendor\laravel\framework\src\Illuminate\Notifications\resources\views\email.blade.php
                    // do this:
                    // {{-- Intro Lines --}}
                    // @foreach ($introLines as $line)
                    //     {!! $line !!}
                    // @endforeach
                    ;
    }
  
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'order_id' => $this->details['order_id']
        ];
    }
}