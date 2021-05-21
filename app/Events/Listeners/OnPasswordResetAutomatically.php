<?php

namespace App\Events\Listeners;

use App\Events\Listeners\Base\NowListener;
use App\Events\PasswordResetAutomaticallyEvent;
use App\Mail\PasswordResetAutomaticallyMailable;
use Illuminate\Support\Facades\Mail;

class OnPasswordResetAutomatically extends NowListener
{
    /**
     * @param PasswordResetAutomaticallyEvent $event
     */
    protected function go($event)
    {
        Mail::send(
            (new PasswordResetAutomaticallyMailable())
                ->to($event->user->preferredEmail(), $event->user->preferredName())
                ->with([
                    'password' => $event->password,
                ])
        );
    }
}