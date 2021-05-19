<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlSessionUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaSessionUrl;

class CsnaSessionUrlRepository extends CrawlSessionUrlRepository
{
    public function modelClass()
    {
        return CsnaSessionUrl::class;
    }
}