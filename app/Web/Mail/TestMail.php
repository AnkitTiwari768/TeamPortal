<?php

namespace App\Web\Mail;
use App\Web\Mail\TestMail;
use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public function build()
    {
        return $this->subject('Test Mail')
                    ->html('<h2>Mail Working ✅</h2><p>This is test mail from Laravel</p>');
    }
}