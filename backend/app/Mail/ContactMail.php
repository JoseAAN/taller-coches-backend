<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userMessage;
    public string $userEmail;
    public string $userName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userMessage, string $userEmail, string $userName)
    {
        $this->userMessage = $userMessage;
        $this->userEmail = $userEmail;
        $this->userName = $userName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->userEmail, $this->userName),
            ],
            subject: '¡Nuevo Mensaje de ' . $this->userName . ' desde la web!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // hice la vista con blade ya que es mucho más cómodo ahora mismo
        return new Content(
            view: 'emails.contact',
            with: [
                'msg' => $this->userMessage,
                'clientEmail' => $this->userEmail,
                'clientName' => $this->userName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
