<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlDatum;
use App\Crawlers\Models\Crawler;
use App\ModelRepositories\Base\ModelRepository;
use Illuminate\Database\Eloquent\Builder;

abstract class CrawlDatumRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlDatum::class;
    }

    protected function searchOn($query, array $search)
    {
        if (isset($search['crawler_id'])) {
            $query->where('crawler_id', $search['crawler_id']);
        }
        if (isset($search['from_crawl_url_id'])) {
            $query->whereHas('crawledData', function (Builder $query) use ($search) {
                $query->where('from_crawl_url_id', $search['from_crawl_url_id']);
            });
        }
        if (isset($search['index'])) {
            $query->where('index', $search['index']);
        }
        return parent::searchOn($query, $search);
    }

    /**
     * @param string $index
     * @return bool
     */
    public function hasIndex(string $index)
    {
        return $this->has(['index' => $index]);
    }

    /**
     * @param Crawler|int $crawler
     * @param string $index
     * @param array $meta
     * @param array $additional
     * @return CrawlDatum|mixed
     */
    public function createWithCrawler($crawler, string $index, array $meta = [], array $additional = [])
    {
        return $this->firstOrCreateWithAttributes(
            [
                'index' => $index,
            ],
            [
                'crawler_id' => $this->retrieveId($crawler),
                'meta' => $meta,
            ] + $additional
        );
    }
}