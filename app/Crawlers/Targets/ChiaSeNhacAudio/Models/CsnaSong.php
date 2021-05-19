<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawlDatum;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CsnaSong
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Models
 * @property CsnaCrawledSong[]|Collection $crawledData
 */
class CsnaSong extends CrawlDatum
{
    protected $table = 'crawl_csna_songs';

    protected function crawledDatumClass()
    {
        return CsnaCrawledSong::class;
    }
}