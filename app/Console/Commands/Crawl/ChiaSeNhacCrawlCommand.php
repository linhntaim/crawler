<?php

namespace App\Console\Commands\Crawl;

class ChiaSeNhacCrawlCommand extends CrawlCommand
{
    protected $signature = 'crawl:chia-se-nhac {--url=}';

    protected function go()
    {
        if ($this->ifOption('url', $url, true)) {
            $this->crawl($url, function ($crawled) {
                if (whenPregMatchAll('/https?\:\/\/[^"\']*\.(flac|mp3|m4a)/', $crawled, $matches)) {
                    foreach ($matches[0] as $url) {
                        $this->download($url);
                    }
                }
            });
        }
    }
}