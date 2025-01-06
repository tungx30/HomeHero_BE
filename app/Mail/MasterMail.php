<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MasterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $view;
    public $data;

    public function __construct($title, $view, $data)
    {
        $this->title = $title;
        $this->view = $view;
        $this->data = $data;
    }
    public function build()
    {
        return $this->subject($this->title)->view($this->view, ['data' => $this->data]);
    }
}
