<?php

namespace App\Notifications;

use App\Mail\OrderMade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderMadeNotif extends Notification
{
    use Queueable;

    /** @var App\Models\HistoryPemesanan $historyPemesanan Header pesanan to send */
    protected $historyPemesanan;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($historyPemesanan)
    {
        $this->historyPemesanan = $historyPemesanan;
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
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        return (new OrderMade($this->historyPemesanan))
                    ->to($notifiable->users_email);
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
            //
        ];
    }
}
