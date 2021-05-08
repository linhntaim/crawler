<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Crawler
 * @package App\Crawlers\Models
 * @property int $id
 * @property string $name
 * @property CrawlSession[]|Collection $sessions
 * @property CrawlUrl[]|Collection $urls
 */
class Crawler extends Model
{
    protected $table = 'crawlers';

    protected $fillable = [
        'name',
    ];

    public function sessions()
    {
        return $this->hasMany(CrawlSession::class, 'crawler_id', 'id');
    }

    public function urls()
    {
        return $this->hasMany(CrawlUrl::class, 'crawler_id', 'id');
    }
}