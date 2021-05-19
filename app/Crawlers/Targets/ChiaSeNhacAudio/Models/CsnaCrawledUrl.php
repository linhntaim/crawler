<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawledUrl;

class CsnaCrawledUrl extends CrawledUrl
{
    protected $table = 'crawl_csna_crawled_urls';

    protected function urlClass()
    {
        return CsnaUrl::class;
    }
}