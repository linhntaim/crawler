<?php

namespace App\Crawlers\Targets\ChiaSeNhacMusic\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlDataRepository;
use App\Crawlers\Targets\ChiaSeNhacMusic\Models\CsnFile;

class CsnFileRepository extends CrawlDataRepository
{
    public function modelClass()
    {
        return CsnFile::class;
    }
}