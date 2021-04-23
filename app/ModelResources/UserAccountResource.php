<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\ModelResources;

use App\Models\User;

/**
 * Class UserAccountResource
 * @package App\ModelResources
 * @mixin User
 */
class UserAccountResource extends UserResource
{
    protected function toCustomArray($request)
    {
        return [
            $this->merge(parent::toCustomArray($request)),
            $this->merge([
                'settings' => $this->preferredSettings()->toArray(),
            ]),
        ];
    }
}
