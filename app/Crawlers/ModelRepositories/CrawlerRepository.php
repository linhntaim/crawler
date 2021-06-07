<?php

namespace App\Crawlers\ModelRepositories;

use App\ModelRepositories\Base\ModelRepository;

abstract class CrawlerRepository extends ModelRepository
{
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