<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;
use App\ModelRepositories\DataExportRepository;

class DataExportController extends ModelApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->modelRepository = new DataExportRepository();
    }

    public function show(Request $request, $id)
    {
        if ($request->has('_download')) {
            return $this->download($request, $id);
        }
        return $this->responseFail();
    }

    private function download(Request $request, $id)
    {
        $export = $this->modelRepository->model($id);

        return $this->responseDownload($export->managedFile);
    }
}