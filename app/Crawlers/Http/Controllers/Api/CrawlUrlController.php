<?php

namespace App\Crawlers\Http\Controllers\Api;

use App\Crawlers\CrawlBot;
use App\Crawlers\CrawlBotFactory;
use App\Crawlers\ModelRepositories\CrawlUrlRepository;
use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;

/**
 * Class CrawlUrlController
 * @package App\Crawlers\Http\Controllers\Api
 * @property CrawlUrlRepository $modelRepository
 */
abstract class CrawlUrlController extends ModelApiController
{
    /**
     * @return string|CrawlBot
     */
    protected abstract function getCrawlBot();

    protected function getCrawlingMax()
    {
        return CrawlBot::CRAWLING_MAX;
    }

    protected function getCrawlingRetrieveMax()
    {
        return CrawlBot::CRAWLING_RETRIEVE_MAX;
    }

    protected function searchParams(Request $request)
    {
        return [
            'from_crawl_url' => 'from_crawl_url_id',
        ];
    }

    protected function urlValidatedRules()
    {
        return ['required', 'url'];
    }

    protected function storeValidatedRules(Request $request)
    {
        return [
            'url' => $this->urlValidatedRules(),
        ];
    }

    protected function storeExecute(Request $request)
    {
        $crawlBot = $this->getCrawlBot();
        if (!($crawlBot instanceof CrawlBot)
            && is_null($crawlBot = CrawlBotFactory::factory($crawlBot))) {
            $this->abort404('Crawler not found');
        }
        return $crawlBot
            ->setCrawlingMax($this->getCrawlingMax())
            ->setCrawlingRetrieveMax($this->getCrawlingRetrieveMax())
            ->crawl($request->input('url'))
            ->getCrawlSession()
            ->crawlUrls()
            ->first();
    }
}