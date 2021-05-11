<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Models;

use App\Crawlers\Models\CrawlData;
use App\Models\HandledFile;

/**
 * Class CsnaFile
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Models
 * @property int $file_id
 * @property int $song_id
 * @property string $index
 * @property array $meta
 * @property HandledFile $file
 * @property CsnaSong $song
 */
class CsnaFile extends CrawlData
{
    protected $table = 'crawl_csna_files';

    protected $fillable = [
        'file_id',
        'song_id',
        'crawl_url_id',
        'crawl_session_id',
        'crawler_id',
        'index',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function file()
    {
        return $this->belongsTo(HandledFile::class, 'file_id', 'id');
    }

    public function song()
    {
        return $this->belongsTo(CsnaSong::class, 'song_id', 'id');
    }
}