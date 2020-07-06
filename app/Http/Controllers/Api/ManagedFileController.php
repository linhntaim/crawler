<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;
use App\ModelRepositories\ManagedFileRepository;

class ManagedFileController extends ModelApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->modelRepository = new ManagedFileRepository();
    }

    public function show(Request $request, $id)
    {
        if ($request->has('_image')) {
            return $this->getImageFile($request, $id);
        }

        return $this->responseFail();
    }

    public function getImageFile(Request $request, $id)
    {
        return $this->modelRepository->model($id)->responseFile();
    }
}