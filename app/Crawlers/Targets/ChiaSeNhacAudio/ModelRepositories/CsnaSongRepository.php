<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDatumRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaSong;

class CsnaSongRepository extends CrawlDatumRepository
{
    public function modelClass()
    {
        return CsnaSong::class;
    }
}