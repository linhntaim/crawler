<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\Crawler;
use App\ModelRepositories\Base\ModelRepository;

class CrawlerRepository extends ModelRepository
{
    public function modelClass()
    {
        return Crawler::class;
    }

    protected function getUniqueKeys()
    {
        return array_merge(parent::getUniqueKeys(), [
            'name',
        ]);
    }

    public function firstOrCreateWithName($name)
    {
        return $this->firstOrCreateWithAttributes([
            'name' => $name,
        ]);
    }
}