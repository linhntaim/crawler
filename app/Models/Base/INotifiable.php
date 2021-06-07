<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Models\Base;

interface INotifiable
{
    public function getKey();

    /**
     * Get the notification routing information for the given driver.
     *
     * @param string $driver
     * @param \Illuminate\Notifications\Notification|null $notification
     * @return mixed
     */
    public function routeNotificationFor($driver, $notification = null);
}