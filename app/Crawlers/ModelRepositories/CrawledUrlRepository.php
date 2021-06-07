<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawledUrl;
use App\Crawlers\Models\CrawlUrl;
use App\ModelRepositories\Base\ModelRepository;

abstract class CrawledUrlRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawledUrl::class;
    }

    /**
     * @param CrawlUrl|int $fromCrawlUrl
     * @param CrawlUrl|int $crawlUrl
     * @return CrawledUrl
     */
    public function createWithFromUrlAndUrl($fromCrawlUrl, $crawlUrl)
    {
        return $this->firstOrCreateWithAttributes([
            'from_crawl_url_id' => $this->retrieveId($fromCrawlUrl),
            'crawl_url_id' => $this->retrieveId($crawlUrl),
        ]);
    }
}