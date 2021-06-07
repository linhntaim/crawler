<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\ModelRepositories\AdminRepository;

trait AdminMiddlewareTrait
{
    protected function getAdmin(Request $request)
    {
        return ($request->hasAdminViaMiddleware() ?
            $request : $request->setAdminViaMiddleware((new AdminRepository())->notStrict()->getById($request->user()->id)))
            ->admin();
    }

    protected function getUser(Request $request)
    {
        return $this->getAdmin($request);
    }
}
