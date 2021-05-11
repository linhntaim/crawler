<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDataRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaFile;

class CsnaFileRepository extends CrawlDataRepository
{
    public function modelClass()
    {
        return CsnaFile::class;
    }
}