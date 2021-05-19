<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawledUrlRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaCrawledUrl;

class CsnaCrawledUrlRepository extends CrawledUrlRepository
{
    public function modelClass()
    {
        return CsnaCrawledUrl::class;
    }
}