<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlSession;
use App\Crawlers\Models\CrawlSessionUrl;
use App\Crawlers\Models\CrawlUrl;
use App\ModelRepositories\Base\ModelRepository;

abstract class CrawlSessionUrlRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlSessionUrl::class;
    }

    /**
     * @param CrawlSession|int $crawlSession
     * @param CrawlUrl|int $crawlUrl
     * @return CrawlSessionUrl
     */
    public function createWithSessionAndUrl($crawlSession, $crawlUrl)
    {
        return $this->firstOrCreateWithAttributes([
            'crawl_session_id' => $this->retrieveId($crawlSession),
            'crawl_url_id' => $this->retrieveId($crawlUrl),
        ]);
    }
}