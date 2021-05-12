<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\Crawler;

class CsnaCrawler extends Crawler
{
    protected function sessionClass()
    {
        return CsnaSession::class;
    }

    protected function urlClass()
    {
        return CsnaUrl::class;
    }
}