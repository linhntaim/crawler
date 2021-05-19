<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawlSessionUrl;

class CsnaSessionUrl extends CrawlSessionUrl
{
    protected $table = 'crawl_csna_session_urls';

    protected function sessionClass()
    {
        return CsnaSession::class;
    }

    protected function urlClass()
    {
        return CsnaUrl::class;
    }
}