<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\ModelRepositories\Base\ModelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class ModelApiController extends ApiController
{
    /**
     * @var ModelRepository|mixed
     */
    protected $modelRepository;

    #region Index
    protected function search(Request $request)
    {
        return [];
    }

    public function index(Request $request)
    {
        $models = $this->modelRepository->search(
            $this->search($request),
            $this->paging(),
            $this->itemsPerPage(),
            $this->sortBy(),
            $this->sortOrder()
        );
        return $this->responseModel($models);
    }
    #endregion

    #region Store
    protected function storeValidatedRules(Request $request)
    {
        return [];
    }

    protected function storeValidated(Request $request)
    {
        $this->validated($request, $this->storeValidatedRules($request));
    }

    protected function storeExecute(Request $request)
    {
        return null;
    }

    public function store(Request $request)
    {
        if ($request->has('_delete')) {
            return $this->bulkDestroy($request);
        }

        $this->storeValidated($request);

        $this->transactionStart();
        return $this->responseModel($this->storeExecute($request));
    }

    #endregion

    public function show(Request $request, $id)
    {
        return $this->responseModel($this->modelRepository->model($id));
    }

    #region Update
    protected function updateValidatedRules(Request $request)
    {
        return [];
    }

    protected function updateValidated(Request $request)
    {
        $this->validated($request, $this->updateValidatedRules($request));
    }

    protected function updateExecute(Request $request)
    {
        return null;
    }

    public function update(Request $request, $id)
    {
        if ($request->has('_delete')) {
            return $this->destroy($request, $id);
        }

        $this->modelRepository->model($id);

        $this->updateValidated($request);

        $this->transactionStart();
        return $this->responseModel($this->updateExecute($request));
    }
    #endregion

    #region Destroy
    protected function bulkDestroyValidatedRules(Request $request)
    {
        return [
            'ids' => 'required|array',
        ];
    }

    protected function bulkDestroyValidated(Request $request)
    {
        $this->validated($request, $this->bulkDestroyValidatedRules($request));
    }

    public function bulkDestroy(Request $request)
    {
        $this->bulkDestroyValidated($request);
        $ids = $request->input('ids');
        $this->transactionStart();
        $this->modelRepository->deleteWithIds($ids);
        return $this->responseSuccess();
    }

    public function destroy(Request $request, $id)
    {
        $this->modelRepository->model($id);
        $ids = [$id];
        $this->transactionStart();
        $this->modelRepository->deleteWithIds($ids);
        return $this->responseSuccess();
    }
    #endregion

    /**
     * @param Model|Collection|LengthAwarePaginator|array $model
     * @return array
     */
    protected function getRespondedModel($model)
    {
        if ($model instanceof Model || $model instanceof Collection || $model instanceof LengthAwarePaginator) {
            $model = $this->modelTransform($model, null, true);
        }
        return is_null($model) ?
            ['model' => null, 'models' => []] :
            (isset($model['model']) || isset($model['models']) ?
                $model : $this->getRespondedDataWithKey($model, Arr::isAssoc($model) ? 'model' : 'models'));
    }

    /**
     * @param Model|Collection|LengthAwarePaginator|array $model
     * @param array $extra
     * @param array $headers
     * @return JsonResponse
     */
    protected function responseModel($model, $extra = [], $headers = [])
    {
        return $this->responseSuccess(array_merge($this->getRespondedModel($model), $extra), $headers);
    }
}