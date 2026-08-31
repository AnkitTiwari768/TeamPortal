<?php

namespace App\Notifications\Email;

use App\Channels\SmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;


/**
 * User Created
 * 
 * This Notification is called when User is Created
 * 
 * Notification Called:
 * 1. Mail
 */

class Signup extends Notification implements ShouldQueue
{
    use Queueable;
    public $user;
    public $action;
    public $plainPassword;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $plainPassword)
    {
        $this->user = $user;
        $this->action = env('REQUEST_URL');
        $this->plainPassword = $plainPassword;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', SmsChannel::class];
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
            ->subject('Signup Successful, FFO')
            ->line('Dear Sir/Madam,')
            ->line('Greetings from FFO,')
            ->line('Your account has been created successfully.')
            ->line('Please follow the URL '.$this->action.'')
            ->line('Your USERNAME is '.$this->user->username.' and PASSWORD is "'. $this->plainPassword .'".')
            ->line('Please login with your credentials and update your Profile.')
            ->line('Thanks & Regards')
            ->line('FFO Team');
    }
    
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'Signup successful',
            'action' => $this->user->id
        ];
    }

    /**
     * Sends notification through sms channel.
     * @param mixed $notifiable 
     * @return string 
     */
    public function toSms($notifiable)
    {
        return sprintf(
            'Dear Sir/Madam, your profile has been created, your user name is '.$this->user->username.' and password is '. $this->plainPassword .' . Please login with details and update your profile.'
        );
    }
}
