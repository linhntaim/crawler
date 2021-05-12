<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlerRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaCrawler;

class CsnaCrawlerRepository extends CrawlerRepository
{
    public function modelClass()
    {
        return CsnaCrawler::class;
    }
}