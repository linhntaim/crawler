<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawlData
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $crawler_id
 * @property int $crawl_session_id
 * @property int $crawl_url_id
 * @property string $index
 * @property array $meta
 * @property Crawler $crawler
 * @property CrawlSession $session
 * @property CrawlUrl $url
 */
abstract class CrawlData extends Model
{
    protected $table = 'crawl_data';

    protected $fillable = [
        'crawl_url_id',
        'crawl_session_id',
        'crawler_id',
        'index',
        'meta',
    ];

    protected $visible = [
        'id',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(CrawlSession::class, 'crawl_session_id', 'id');
    }

    public function url()
    {
        return $this->belongsTo(CrawlUrl::class, 'crawl_url_id', 'id');
    }
}