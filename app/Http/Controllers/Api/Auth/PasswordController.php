<?php

/**
 * Base - Any modification needs to be approved, except the space inside the block of TODO
 */

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\ModelApiController;
use App\Http\Requests\Request;
use App\ModelRepositories\Base\IUserRepository;
use App\ModelRepositories\PasswordResetRepository;
use App\Models\User;
use App\Vendors\Illuminate\Support\Str;
use Closure;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

/**
 * Class PasswordController
 * @package App\Http\Controllers\Api\Auth
 * @property PasswordResetRepository $modelRepository
 */
abstract class PasswordController extends ModelApiController
{
    /**
     * @var IUserRepository
     */
    protected $userRepository;

    public function __construct()
    {
        parent::__construct();

        if ($userRepositoryClass = $this->getUserRepositoryClass()) {
            $this->userRepository = new $userRepositoryClass;
        }
    }

    protected function modelRepositoryClass()
    {
        return PasswordResetRepository::class;
    }

    /**
     * @return string
     */
    protected abstract function getUserRepositoryClass();

    protected function getPasswordMinLength()
    {
        return $this->userRepository->newModel(false)->getPasswordMinLength();
    }

    /**
     * @return PasswordBroker
     */
    protected function broker()
    {
        return Password::broker();
    }

    protected function brokerGetUser(array $credentials)
    {
        return $this->broker()->getUser($credentials);
    }

    protected function brokerTokenExists(CanResetPassword $user, $token)
    {
        return $this->broker()->tokenExists($user, $token);
    }

    protected function brokerSendResetLink(array $credentials, Closure $callback = null)
    {
        return $this->broker()->sendResetLink($credentials, $callback ? $callback : function ($user, $token) {
            $this->userRepository->model($user)->sendPasswordResetNotification($token);
        });
    }

    protected function brokerReset(array $credentials, Closure $callback)
    {
        return $this->broker()->reset($credentials, $callback);
    }

    public function index(Request $request)
    {
        if ($request->has('_reset')) {
            return $this->indexReset($request);
        }

        return $this->responseFail();
    }

    protected function indexReset(Request $request)
    {
        $this->validated($request, [
            'token' => 'required',
        ]);

        $email = $this->modelRepository->getEmailByToken($request->input('token'));
        if (empty($email)) {
            $this->abort404();
        }

        $user = $this->brokerGetUser([
            'email' => $email,
        ]);
        if (is_null($user)) {
            return $this->responseFail(trans(Password::INVALID_USER));
        }
        if (!$this->brokerTokenExists($user, $request->input('token'))) {
            $this->abort404();
        }

        return $this->responseModel([
            'email' => $email,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('_forgot')) {
            return $this->forgot($request);
        }
        if ($request->has('_reset')) {
            return $this->reset($request);
        }

        return $this->responseFail();
    }

    protected function forgot(Request $request)
    {
        $this->validated($request, [
            'email' => 'required|email',
        ]);

        $response = $this->brokerSendResetLink([
            'email' => $request->input('email'),
        ]);

        return $response == Password::RESET_LINK_SENT
            ? $this->responseSuccess()
            : $this->responseFail(trans($response));
    }

    protected function resetValidatedRules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:' . $this->getPasswordMinLength() . '|confirmed',
        ];
    }

    protected function reset(Request $request)
    {
        $this->validated($request, $this->resetValidatedRules());

        $response = $this->brokerReset(
            [
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'token' => $request->input('token'),
            ],
            function ($user, $password) {
                $this->afterReset($user, $password);
            }
        );

        return $response == Password::PASSWORD_RESET
            ? $this->responseSuccess()
            : $this->responseFail(trans($response));
    }

    protected function afterReset(User $user, $password)
    {
        $user->password = Str::hash($password);
        $user->save();

        event(new PasswordReset($user));
    }
}
