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
abstract class Crawler extends Model
{
    protected $table = 'crawlers';

    protected $fillable = [
        'name',
    ];

    protected abstract function sessionClass();

    protected abstract function urlClass();

    public function sessions()
    {
        return $this->hasMany($this->sessionClass(), 'crawler_id', 'id');
    }

    public function urls()
    {
        return $this->hasMany($this->urlClass(), 'crawler_id', 'id');
    }
}