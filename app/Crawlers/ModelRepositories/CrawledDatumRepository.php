<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlDatum;
use App\Crawlers\Models\CrawledDatum;
use App\Crawlers\Models\CrawlUrl;
use App\ModelRepositories\Base\ModelRepository;

abstract class CrawledDatumRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawledDatum::class;
    }

    /**
     * @param CrawlUrl|int $fromCrawlUrl
     * @param CrawlDatum|int $crawlDatum
     * @return CrawledDatum
     */
    public function createWithFromUrlAndDatum($fromCrawlUrl, $crawlDatum)
    {
        return $this->firstOrCreateWithAttributes([
            'from_crawl_url_id' => $this->retrieveId($fromCrawlUrl),
            'crawl_datum_id' => $this->retrieveId($crawlDatum),
        ]);
    }
}