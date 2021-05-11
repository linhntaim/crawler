<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaUrl;

class CsnaUrlRepository extends CrawlUrlRepository
{
    public function modelClass()
    {
        return CsnaUrl::class;
    }
}