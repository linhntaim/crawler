<?php

namespace App\Crawlers\Targets\ChiaSeNhacMusic\Models;

use App\Crawlers\Models\CrawlData;
use App\Models\HandledFile;

/**
 * Class CsnFile
 * @package App\Crawlers\Targets\ChiaSeNhacMusic\Models
 * @property int $file_id
 * @property int $song_id
 * @property string $index
 * @property array $meta
 * @property HandledFile $file
 * @property CsnSong $song
 */
class CsnFile extends CrawlData
{
    protected $table = 'crawl_data_csn_files';

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
        return $this->belongsTo(CsnSong::class, 'song_id', 'id');
    }
}