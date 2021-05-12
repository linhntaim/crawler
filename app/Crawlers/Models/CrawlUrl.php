<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;
use App\Utils\ClientSettings\Facade;

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
abstract class CrawlUrl extends Model
{
    public const STATUS_FRESH = 1;
    public const STATUS_CRAWLING = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_UNCOMPLETED = 4;
    public const STATUS_COMPLETED = 5;

    protected $fillable = [
        'crawler_id',
        'crawl_url_id',
        'crawl_session_id',
        'status',
        'index',
        'url',
    ];

    protected $visible = [
        'id',
        'url',
        'status',
        'sd_st_created_at',
    ];

    protected $appends = [
        'sd_st_created_at',
    ];

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(CrawlSession::class, 'crawl_session_id', 'id');
    }

    public function getSdStCreatedAtAttribute()
    {
        return Facade::dateTimer()->compound('shortDate', ' ', 'shortTime', $this->attributes['created_at']);
    }
}