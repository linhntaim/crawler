<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\Crawler;
use App\Crawlers\Models\CrawlUrl;
use App\ModelRepositories\Base\ModelRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlUrlRepository
 * @package App\Crawlers\ModelRepositories
 */
abstract class CrawlUrlRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlUrl::class;
    }

    protected function searchOn($query, array $search)
    {
        if (isset($search['from_crawl_url_id'])) {
            $query->whereHas('fromCrawledUrls', function (Builder $query) use ($search) {
                $query->where('from_crawl_url_id', $search['from_crawl_url_id']);
            });
        }
        return parent::searchOn($query, $search);
    }

    protected function getUniqueKeys()
    {
        return array_merge(parent::getUniqueKeys(), [
            'index',
        ]);
    }

    /**
     * @param Crawler|int $crawler
     * @param int $divided
     * @param int $order
     * @return CrawlUrl[]|Collection
     * @throws
     */
    public function getNotCompletedByCrawlerAlongIdWithDividedOrder($crawler, int $divided, int $order)
    {
        return $this->catch(function () use ($crawler, $divided, $order) {
            return $this->query()
                ->where('crawler_id', $this->retrieveId($crawler))
                ->where('status', '<>', CrawlUrl::STATUS_COMPLETED)
                ->whereRaw('(id - ?) % ? = 0', [$order, $divided])
                ->orderBy('status')
                ->get();
        });
    }

    protected function generateIndexFromUrl(string $url)
    {
        $parsed = parse_url($url);
        return sprintf(
            '%s.%s',
            md5($parsed['host']),
            md5(sprintf('%s?%s#%s', $parsed['path'] ?? '', $parsed['query'] ?? '', $parsed['hash'] ?? ''))
        );
    }

    /**
     * @param Crawler|int $crawler
     * @param string $url
     * @return CrawlUrl
     */
    public function createWithCrawler($crawler, string $url)
    {
        return $this->firstOrCreateWithAttributes(
            [
                'index' => $this->generateIndexFromUrl($url),
            ],
            [
                'crawler_id' => $this->retrieveId($crawler),
                'status' => CrawlUrl::STATUS_FRESH,
                'url' => $url,
            ]
        );
    }

    public function updateStatus($status)
    {
        return $this->updateWithAttributes([
            'status' => $status,
        ]);
    }
}