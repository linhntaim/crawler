<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawledDatum;

/**
 * Class CsnaCrawledFile
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Models
 * @property CsnaFile $data
 */
class CsnaCrawledFile extends CrawledDatum
{
    protected $table = 'crawl_csna_crawled_files';

    protected function urlClass()
    {
        return CsnaUrl::class;
    }

    protected function datumClass()
    {
        return CsnaFile::class;
    }
}