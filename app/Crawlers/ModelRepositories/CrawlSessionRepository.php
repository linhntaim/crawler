<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlSession;
use App\ModelRepositories\Base\ModelRepository;

class CrawlSessionRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlSession::class;
    }

    public function createWithCrawler($crawler)
    {
        return $this->createWithAttributes([
            'crawler_id' => $this->retrieveId($crawler),
        ]);
    }
}