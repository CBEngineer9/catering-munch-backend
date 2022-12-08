<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Env;

class OrderMade extends Mailable
{
    use Queueable, SerializesModels;

    /** @var App\Models\HistoryPemesanan $historyPemesanan Header pesanan to send */
    protected $historyPemesanan;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($historyPemesanan)
    {
        $this->historyPemesanan = $historyPemesanan;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            // TODO change
            from: new Address(env('MAIL_FROM_ADDRESS','cbealexanderkevin@gmail.com'), env('MAIL_FROM_NAME','Alexander kevin')),
            subject: 'New Order Made',  
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
