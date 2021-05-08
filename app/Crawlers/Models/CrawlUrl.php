<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawlUrl
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $status
 * @property string $index
 * @property string $url
 * @property Crawler $crawler
 * @property CrawlSession $session
 */
class CrawlUrl extends Model
{
    public const STATUS_FRESH = 1;
    public const STATUS_CRAWLING = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_UNCOMPLETED = 4;
    public const STATUS_COMPLETED = 5;

    protected $table = 'crawl_urls';

    protected $fillable = [
        'crawler_id',
        'crawl_session_id',
        'status',
        'index',
        'url',
    ];

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(CrawlSession::class, 'crawl_session_id', 'id');
    }
}