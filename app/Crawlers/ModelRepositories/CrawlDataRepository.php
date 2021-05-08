<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlData;
use App\ModelRepositories\Base\ModelRepository;

abstract class CrawlDataRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlData::class;
    }
}