<?php

namespace App\Crawlers\Targets\ChiaSeNhacMusic\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDataRepository;
use App\Crawlers\Targets\ChiaSeNhacMusic\Models\CsnSong;

class CsnSongRepository extends CrawlDataRepository
{
    public function modelClass()
    {
        return CsnSong::class;
    }
}