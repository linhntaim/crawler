<?php

namespace App\ModelRepositories;

use App\ModelRepositories\Base\DependedRepository;
use App\Models\Admin;
use App\Utils\HandledFiles\Filer\ImageFiler;

/**
 * Class UserRepository
 * @package App\ModelRepositories
 * @property Admin $model
 * @method Admin getById($id, callable $callback = null)
 */
class AdminRepository extends DependedRepository
{
    public function __construct($id = null)
    {
        parent::__construct('user', $id);
    }

    public function modelClass()
    {
        return Admin::class;
    }

    public function createWithAttributes(array $attributes = [], array $userAttributes = [], array $userSocialAttribute = [])
    {
        $attributes['user_id'] = (new UserRepository())->createWithAttributes($userAttributes, $userSocialAttribute)->id;
        return parent::createWithAttributes($attributes);
    }

    public function updateAvatar($imageFile)
    {
        return $this->updateWithAttributes([
            'avatar_id' => (new HandledFileRepository())->createWithFiler(
                (new ImageFiler())
                    ->fromExisted($imageFile, false, false)
                    ->moveToPublic()
            )->id,
        ]);
    }

    /**
     * @param array $ids
     * @return bool
     * @throws
     */
    public function deleteWithIds(array $ids)
    {
        return $this->queryDelete(
            $this->dependedWhere(function ($query) {
                $query->noneProtected();
            })
                ->queryByIds($ids)
        );
    }
}
