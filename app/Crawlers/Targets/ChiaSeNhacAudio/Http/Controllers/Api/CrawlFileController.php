<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api;

use App\Crawlers\Http\Controllers\Api\CrawlDataController;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaFileRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaUrlRepository;

/**
 * Class CrawlFileController
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api
 * @property CsnaUrlRepository $modelRepository
 */
class CrawlFileController extends CrawlDataController
{
    protected function modelRepositoryClass()
    {
        return CsnaFileRepository::class;
    }
}