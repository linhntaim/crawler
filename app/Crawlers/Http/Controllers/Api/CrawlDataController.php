<?php

namespace App\Crawlers\Http\Controllers\Api;

use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;

abstract class CrawlDataController extends ModelApiController
{
    protected function searchParams(Request $request)
    {
        return [
            'from_crawl_url' => 'from_crawl_url_id',
        ];
    }
}