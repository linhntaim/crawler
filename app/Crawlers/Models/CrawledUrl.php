<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawledUrl
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $from_crawl_url_id
 * @property int $crawl_url_id
 * @property CrawlUrl $fromUrl
 * @property CrawlUrl $url
 */
abstract class CrawledUrl extends Model
{
    protected $table = 'crawl_crawled_urls';

    protected $fillable = [
        'from_crawl_url_id',
        'crawl_url_id',
    ];

    protected $visible = [
        'id',
    ];

    protected abstract function urlClass();

    public function fromUrl()
    {
        return $this->belongsTo($this->urlClass(), 'from_crawl_url_id', 'id');
    }

    public function url()
    {
        return $this->belongsTo($this->urlClass(), 'crawl_url_id', 'id');
    }
}