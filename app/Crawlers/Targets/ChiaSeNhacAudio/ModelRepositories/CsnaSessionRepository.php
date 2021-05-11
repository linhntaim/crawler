<?php

namespace App\Crawlers\Targets\ChiaSeNhacAudio\ModelRepositories;

use App\Crawlers\ModelRepositories\CrawlSessionRepository;
use App\Crawlers\Targets\ChiaSeNhacAudio\Models\CsnaSession;

class CsnaSessionRepository extends CrawlSessionRepository
{
    public function modelClass()
    {
        return CsnaSession::class;
    }
}