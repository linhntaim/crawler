<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawledDatum;

/**
 * Class CsnaCrawledSong
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Models
 * @property CsnaSong $datum
 */
class CsnaCrawledSong extends CrawledDatum
{
    protected $table = 'crawl_csna_crawled_songs';

    protected function urlClass()
    {
        return CsnaUrl::class;
    }

    protected function datumClass()
    {
        return CsnaSong::class;
    }
}