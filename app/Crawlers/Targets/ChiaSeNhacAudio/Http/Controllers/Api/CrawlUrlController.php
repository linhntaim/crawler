<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api;

use App\Crawlers\Http\Controllers\Api\CrawlUrlController as BaseCrawlUrlController;
use App\Crawlers\Targets\ChiaSeNhacAudio\CrawlBot;
use App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories\CsnaUrlRepository;

/**
 * Class CrawlUrlController
 * @package App\Crawlers\Targets\ChiaSeNhacAudio\Http\Controllers\Api
 * @property CsnaUrlRepository $modelRepository
 */
class CrawlUrlController extends BaseCrawlUrlController
{
    protected function modelRepositoryClass()
    {
        return CsnaUrlRepository::class;
    }

    protected function getCrawlBot()
    {
        return CrawlBot::NAME;
    }

    protected function urlValidatedRules()
    {
        return array_merge(parent::urlValidatedRules(), [
            'regex:/^https?:\/\/(|.+\.)chiasenhac.vn/i',
        ]);
    }
}