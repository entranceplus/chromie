<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LinksChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $changedLinksArr;
    public function __construct($changedLinksArr)
    {
        $this->changedLinksArr = $changedLinksArr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.linksChanged');
    }
}
