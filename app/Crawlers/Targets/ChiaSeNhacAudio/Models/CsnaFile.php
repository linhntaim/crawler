<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawlDatum;
use App\Models\HandledFile;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CsnaFile
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Models
 * @property CsnaCrawledFile[]|Collection $crawledData
 * @property int $song_id
 * @property int $file_id
 * @property CsnaSong $song
 * @property HandledFile $file
 */
class CsnaFile extends CrawlDatum
{
    protected $table = 'crawl_csna_files';

    protected $fillable = [
        'crawler_id',
        'index',
        'meta',

        'song_id',
        'file_id',
    ];

    protected function crawledDatumClass()
    {
        return CsnaCrawledFile::class;
    }

    public function song()
    {
        return $this->belongsTo(CsnaSong::class, 'song_id', 'id');
    }

    public function file()
    {
        return $this->belongsTo(HandledFile::class, 'file_id', 'id');
    }
}