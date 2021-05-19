<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawledDatumRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaCrawledFile;

class CsnaCrawledFileRepository extends CrawledDatumRepository
{
    public function modelClass()
    {
        return CsnaCrawledFile::class;
    }
}