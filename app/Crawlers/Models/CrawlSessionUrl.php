<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawlSessionUrl
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $crawl_session_id
 * @property int $crawl_url_id
 * @property CrawlSession $session
 * @property CrawlUrl $url
 */
abstract class CrawlSessionUrl extends Model
{
    protected $table = 'crawl_session_urls';

    protected $fillable = [
        'crawl_session_id',
        'crawl_url_id',
    ];

    protected $visible = [
        'id',
    ];

    protected abstract function sessionClass();

    protected abstract function urlClass();

    public function session()
    {
        return $this->belongsTo($this->sessionClass(), 'crawl_session_id', 'id');
    }

    public function url()
    {
        return $this->belongsTo($this->urlClass(), 'crawl_url_id', 'id');
    }
}