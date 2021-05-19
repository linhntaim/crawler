<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawledDatumRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaCrawledSong;

class CsnaCrawledSongRepository extends CrawledDatumRepository
{
    public function modelClass()
    {
        return CsnaCrawledSong::class;
    }
}