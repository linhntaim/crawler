<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlerInstance;
use App\ModelRepositories\Base\ModelRepository;

class CrawlerInstanceRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlerInstance::class;
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