<?php

namespace App\Crawlers\Models;

use App\ModelCasts\SafeArrayCast;
use App\Models\Base\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CrawlData
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $crawler_id
 * @property string $index
 * @property array $meta
 * @property Crawler $crawler
 * @property CrawledDatum[]|Collection $crawledData
 */
abstract class CrawlDatum extends Model
{
    protected $table = 'crawl_data';

    protected $fillable = [
        'crawler_id',
        'index',
        'meta',
    ];

    protected $visible = [
        'id',
        'meta',
    ];

    protected $casts = [
        'meta' => SafeArrayCast::class,
    ];

    public $timestamps = false;

    public function crawler()
    {
        return $this->belongsTo(Crawler::class, 'crawler_id', 'id');
    }

    protected abstract function crawledDatumClass();

    public function crawledData()
    {
        return $this->hasMany($this->crawledDatumClass(), 'crawl_datum_id', 'id');
    }
}