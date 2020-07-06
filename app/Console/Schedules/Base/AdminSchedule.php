<?php

namespace App\Console\Schedules;

use App\Utils\ClientSettings\AdminSettingsHandleTrait;

abstract class AdminSchedule extends Schedule
{
    use AdminSettingsHandleTrait;
}