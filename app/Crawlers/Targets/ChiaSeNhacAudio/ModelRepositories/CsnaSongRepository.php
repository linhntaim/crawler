<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDataRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaSong;

class CsnaSongRepository extends CrawlDataRepository
{
    public function modelClass()
    {
        return CsnaSong::class;
    }
}