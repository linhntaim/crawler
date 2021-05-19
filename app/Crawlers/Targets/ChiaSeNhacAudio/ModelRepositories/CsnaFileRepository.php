<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDatumRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaFile;

class CsnaFileRepository extends CrawlDatumRepository
{
    public function modelClass()
    {
        return CsnaFile::class;
    }
}