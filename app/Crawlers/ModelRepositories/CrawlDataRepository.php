<?php

namespace App\Crawlers\ModelRepositories;

use App\Crawlers\Models\CrawlData;
use App\ModelRepositories\Base\ModelRepository;

abstract class CrawlDataRepository extends ModelRepository
{
    public function modelClass()
    {
        return CrawlData::class;
    }

    protected function searchOn($query, array $search)
    {
        if (!empty($search['index'])) {
            $query->where('index', $search['index']);
        }
        return parent::searchOn($query, $search);
    }

    /**
     * @param string $index
     * @return bool
     */
    public function hasIndex($index)
    {
        return $this->has(['index' => $index]);
    }
}