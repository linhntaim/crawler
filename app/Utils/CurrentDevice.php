<?php

namespace App\Utils;

use App\Models\Device;

class CurrentDevice
{
    /**
     * @var Device
     */
    protected static $device;

    public static function set(Device $device = null)
    {
        static::$device = $device;
    }

    public static function get()
    {
        return static::$device;
    }

    public static function getId()
    {
        return empty(static::$device) ? null : static::$device->id;
    }
}