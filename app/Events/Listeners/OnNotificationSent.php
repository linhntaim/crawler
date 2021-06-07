<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Events\Listeners;

use App\Events\Listeners\Base\NowListener;
use App\Models\Base\INotifiable;
use App\Models\Base\IUser;
use App\Notifications\Base\Notification;
use App\Vendors\Illuminate\Support\Facades\App;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class OnNotificationSent extends NowListener
{
    /**
     * @param NotificationSent $event
     */
    protected function go($event)
    {
        $notifiable = $this->getNotifiable($event);
        $this->getNotification($event)->afterNotifying($event->channel, $notifiable);
        if (App::runningInDebug()) {
            Log::info(
                sprintf(
                    '[%s] was sent to [%s(%s)] via [%s].',
                    get_class($this->getNotification($event)),
                    get_class($notifiable),
                    $notifiable->getKey(),
                    $event->channel
                )
            );
        }
    }

    /**
     * @param NotificationSent $event
     * @return INotifiable
     */
    protected function getNotifiable($event)
    {
        return $event->notifiable;
    }

    /**
     * @param NotificationSent $event
     * @return Notification
     */
    protected function getNotification($event)
    {
        return $event->notification;
    }
}
