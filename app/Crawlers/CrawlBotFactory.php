<?php

namespace App\Crawlers;

use ReflectionClass;

class CrawlBotFactory
{
    protected static $crawlBotClasses = [
        \App\Crawlers\Targets\ChiaSeNhacAudio\CrawlBot::class,
    ];

    /**
     * @param string $name
     * @param string|null $instance
     * @return CrawlBot|null
     * @throws
     */
    public static function factory(string $name, string $instance = null)
    {
        foreach (static::$crawlBotClasses as $crawlBotClass) {
            if ($name == (new ReflectionClass($crawlBotClass))->getConstant('NAME')) {
                return static::createCrawlBot($crawlBotClass, $instance);
            }
        }
        return null;
    }

    /**
     * @param string $crawlBotClass
     * @param string|null $instance
     * @return CrawlBot
     */
    protected static function createCrawlBot(string $crawlBotClass, string $instance = null)
    {
        return new $crawlBotClass($instance);
    }

    /**
     * @return array
     * @throws
     */
    public static function availableCrawlBots()
    {
        return array_map(function ($crawlBotClass) {
            return (new ReflectionClass($crawlBotClass))->getConstant('NAME');
        }, static::$crawlBotClasses);
    }
}