<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;
use App\ModelRepositories\HandledFileRepository;
use App\Utils\HandledFiles\Filer\ImageFiler;

/**
 * Class HandledFileController
 * @package App\Http\Controllers\Api
 * @property HandledFileRepository $modelRepository
 */
class HandledFileController extends ModelApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->modelRepository = new HandledFileRepository();
    }

    public function show(Request $request, $id)
    {
        if ($request->has('_inline')) {
            return $this->getInlineFile($request, $id);
        }
        return $this->responseFail();
    }

    public function getInlineFile(Request $request, $id)
    {
        return $this->modelRepository->model($id)->responseFile();
    }

    public function store(Request $request)
    {
        if ($request->has('_azure')) {
            return $this->storeByAzure($request);
        }
        return parent::store($request); // TODO: Change the autogenerated stub
    }

    public function storeByAzure(Request $request)
    {
        return $this->modelRepository->createWithFiler(
            (new ImageFiler())
                ->fromExisted($request->file('test'), false, false)
                ->moveToCloud()
        );
    }
}
