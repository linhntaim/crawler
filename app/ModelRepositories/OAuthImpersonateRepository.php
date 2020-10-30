<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\ModelRepositories;

use App\ModelRepositories\Base\ModelRepository;
use App\Models\OAuthImpersonate;

/**
 * Class OAuthImpersonateRepository
 * @package App\ModelRepositories
 */
class OAuthImpersonateRepository extends ModelRepository
{
    public function modelClass()
    {
        return OAuthImpersonate::class;
    }

    /**
     * @param string $impersonateToken
     * @return OAuthImpersonate
     */
    public function getByImpersonateToken($impersonateToken)
    {
        return $this->first(
            $this->query()
                ->where('impersonate_token', $impersonateToken)
        );
    }

    /**
     * @param int $userId
     * @param string $accessTokenId
     * @return OAuthImpersonate
     */
    public function getByUserIdAndAccessTokenId($userId, $accessTokenId)
    {
        return $this->first(
            $this->query()
                ->where('user_id', $userId)
                ->where('access_token_id', $accessTokenId)
        );
    }
}
