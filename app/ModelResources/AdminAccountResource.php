<?php

namespace App\ModelResources;

use App\Models\Admin;

/**
 * Class RoleResource
 * @package App\ModelResources
 * @mixin Admin
 */
class AdminAccountResource extends AdminResource
{
    public function toCustomArray($request)
    {
        return [
            $this->merge(parent::toCustomArray($request)),
            $this->merge([
                'settings' => $this->preferredSettings()->toArray(),
            ]),
        ];
    }
}