<?php

namespace App\ModelRepositories;

use App\Exports\Export;
use App\Jobs\ExportJob;
use App\ModelRepositories\Base\ModelRepository;
use App\Models\DataExport;

/**
 * Class DataExportRepository
 * @package App\ModelRepositories
 * @method DataExport model($id = null)
 */
class DataExportRepository extends ModelRepository
{
    public function modelClass()
    {
        return DataExport::class;
    }

    protected function searchOn($query, array $search)
    {
        if (!empty($search['names'])) {
            $query->whereIn('name', $search['names']);
        }
        return parent::searchOn($query, $search);
    }

    public function createWithAttributesAndExport(array $attributes, Export $export)
    {
        $attributes['name'] = $export->getName();
        $attributes['state'] = DataExport::STATE_EXPORTING;
        $attributes['payload'] = serialize($export);
        $this->createWithAttributes($attributes);
        ExportJob::dispatch($this->model, $export);
        return $this->model;
    }
}