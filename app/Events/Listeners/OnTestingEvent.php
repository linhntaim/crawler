<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Events\Listeners;

use App\Events\TestEvent;
use App\Events\Listeners\Base\NowListener;
use Illuminate\Support\Facades\Log;

class OnTestingEvent extends NowListener
{
    /**
     * @param TestEvent $event
     */
    protected function go($event)
    {
        Log::info(sprintf('Called to %s.', static::class));
    }
}
