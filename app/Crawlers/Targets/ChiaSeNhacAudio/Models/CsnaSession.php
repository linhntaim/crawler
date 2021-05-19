<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawlSession;

class CsnaSession extends CrawlSession
{
    protected $table = 'crawl_csna_sessions';

    protected function urlClass()
    {
        return CsnaUrl::class;
    }

    protected function sessionUrlClass()
    {
        return CsnaSessionUrl::class;
    }
}