<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\Crawler;
use App\Crawlers\Models\CrawlUrl;
use App\ModelRepositories\Base\ModelRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlUrlRepository
 * @package App\Crawlers\ModelRepositories
 * @method CrawlUrl firstOrCreateWithAttributes(array $attributes = [], array $values = [])
 */
class CrawlUrlRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlUrl::class;
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
    public function getFreshByCrawlerAlongIdWithDividedOrder($crawler, int $divided, int $order)
    {
        return $this->catch(function () use ($crawler, $divided, $order) {
            return $this->query()
                ->where('crawler_id', $this->retrieveId($crawler))
                ->where('status', CrawlUrl::STATUS_FRESH)
                ->whereRaw('(id - ?) % ? = 0', [$order, $divided])
                ->get();
        });
    }

    public function updateStatus($status)
    {
        return $this->updateWithAttributes([
            'status' => $status,
        ]);
    }
}