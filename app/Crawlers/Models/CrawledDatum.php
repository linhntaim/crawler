<?php

namespace App\Crawlers\Models;

use App\Models\Base\Model;

/**
 * Class CrawledData
 * @package App\Crawlers\Models
 * @property int $id
 * @property int $from_crawl_url_id
 * @property int $crawl_data_id
 * @property CrawlUrl $fromUrl
 * @property CrawlDatum $datum
 */
abstract class CrawledDatum extends Model
{
    protected $table = 'crawled_data';

    protected $fillable = [
        'from_crawl_url_id',
        'crawl_datum_id',
    ];

    protected $visible = [
        'id',
    ];

    protected abstract function urlClass();

    protected abstract function datumClass();

    public function fromUrl()
    {
        return $this->belongsTo($this->urlClass(), 'from_crawl_url_id', 'id');
    }

    public function datum()
    {
        return $this->belongsTo($this->datumClass(), 'crawl_datum_id', 'id');
    }
}