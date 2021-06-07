<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;
use App\Utils\ClientSettings\Facade;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlUrl
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $status
 * @property string $index
 * @property string $url
 * @property Crawler $crawler
 * @property CrawledUrl[]|Collection $fromCrawledUrls
 * @property CrawledUrl[]|Collection $crawledUrls
 */
abstract class CrawlUrl extends Model
{
    public const STATUS_FRESH = 1;
    public const STATUS_CRAWLING = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_UNCOMPLETED = 4;
    public const STATUS_COMPLETED = 5;

    protected $table = 'crawl_urls';

    protected $fillable = [
        'crawler_id',
        'status',
        'index',
        'url',
    ];

    protected $visible = [
        'id',
        'url',
        'status',
    ];

    public $timestamps = false;

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    protected abstract function crawledUrlClass();

    public function fromCrawledUrls()
    {
        return $this->hasMany($this->crawledUrlClass(), 'crawl_url_id', 'id');
    }

    public function crawledUrls()
    {
        return $this->hasMany($this->crawledUrlClass(), 'from_crawl_url_id', 'id');
    }
}