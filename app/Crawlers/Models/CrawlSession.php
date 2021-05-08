<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlSession
 * @package App\Crawlers\Models
 * @property int $id
 * @property Crawler $crawler
 * @property CrawlUrl[]|Collection $urls
 */
class CrawlSession extends Model
{
    protected $table = 'crawl_sessions';

    protected $fillable = [
        'crawler_id',
    ];

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    public function urls()
    {
        return $this->hasMany(CrawlUrl::class, 'crawl_session_id', 'id');
    }
}