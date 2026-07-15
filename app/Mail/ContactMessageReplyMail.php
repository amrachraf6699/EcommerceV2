<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $subjectLine,
        public string $replyBody
    ) {
        $this->onQueue('mail')->afterCommit();
    }

    public function build(): self
    {
        return $this
            ->subject($this->subjectLine)
            ->view('emails.contact-message-reply');
    }
}
