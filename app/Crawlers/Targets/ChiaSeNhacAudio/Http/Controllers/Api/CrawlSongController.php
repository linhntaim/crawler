<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api;

use App\Crawlers\Http\Controllers\Api\CrawlDataController;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaSongRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaUrlRepository;

/**
 * Class CrawlSongController
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api
 * @property CsnaUrlRepository $modelRepository
 */
class CrawlSongController extends CrawlDataController
{
    protected function modelRepositoryClass()
    {
        return CsnaSongRepository::class;
    }
}