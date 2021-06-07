<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlSession
 * @package App\Crawlers\Models
 * @property int $id
 * @property Crawler $crawler
 * @property CrawlUrl[]|Collection $crawlUrls
 */
abstract class CrawlSession extends Model
{
    protected $table = 'crawl_sessions';

    protected $fillable = [
        'crawler_id',
    ];

    protected $visible = [
        'id',
    ];

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    protected abstract function urlClass();

    protected abstract function sessionUrlClass();

    /**
     * @return CrawlSessionUrl
     */
    protected function newSessionUrlModel()
    {
        $sessionUrlClass = $this->sessionUrlClass();
        return new $sessionUrlClass;
    }

    public function crawlUrls()
    {
        return $this->belongsToMany($this->urlClass(), $this->newSessionUrlModel()->getTable(), 'crawl_session_id', 'crawl_url_id', 'id', 'id');
    }
}