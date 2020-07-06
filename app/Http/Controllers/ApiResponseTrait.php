<?php

namespace App\Http\Controllers;

use App\Configuration;
use App\Exceptions\Exception;
use App\Exceptions\UnhandledException;
use App\Exceptions\UserException;
use App\Http\Requests\Request;
use App\Utils\ConfigHelper;
use App\Utils\Helper;
use App\Utils\LogHelper;
use Closure;
use Exception as BaseException;
use Illuminate\Http\JsonResponse;
use League\OAuth2\Server\Exception\OAuthServerException;
use Throwable;

trait ApiResponseTrait
{
    protected static $extraResponse = null;

    public static function addBlockResponseMessage($message, $fresh = false)
    {
        if (!empty(static::$extraResponse)) {
            static::$extraResponse = [];
        }
        if ($fresh || !isset(static::$extraResponse['_block']) || static::$extraResponse['_block'] == null) {
            static::$extraResponse['_block'] = [];
        }
        static::$extraResponse['_block'][] = $message;
    }

    public static function addErrorResponseMessage($level, $data)
    {
        if (!empty(static::$extraResponse)) {
            static::$extraResponse = [];
        }
        static::$extraResponse['_error'] = [
            'level' => $level,
            'data' => Helper::default($data, null),
        ];
    }

    /**
     * @param array|null $data
     * @param Throwable|array|string|null $message
     * @return array
     */
    protected static function payload($data = null, $message = null)
    {
        $debug = null;
        $debugMode = config('app.debug');

        if ($message instanceof Throwable) {
            $exception = $message;
            $debug = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
            if ($exception instanceof OAuthServerException) {
                $exception = new UserException(
                    trans('passport.' . $exception->getErrorType() . ($exception->getCode() == 8 ? '_refresh_token' : '')),
                    0,
                    $exception
                );
            }
            if (!($exception instanceof Exception)) {
                $exception = new UnhandledException($exception->getMessage(), 0, $exception);
            }

            $message = $exception->getMessages();
            static::addErrorResponseMessage($exception->getLevel(), $exception->getAttachedData());
        }

        return [
            '_messages' => empty($message) ? null : (array)$message,
            '_data' => $data,
            '_extra' => static::$extraResponse,
            '_exception' => $debugMode ? $debug : null,
        ];
    }

    public static function failPayload($data = null, $message = null)
    {
        return array_merge(static::payload($data, $message), [
            '_status' => false,
        ]);
    }

    public static function successPayload($data = null, $message = null)
    {
        return array_merge(static::payload($data, $message), [
            '_status' => true,
        ]);
    }

    protected function withInlineMiddleware()
    {
        $this->middleware(function (Request $request, Closure $next) {
            $this->inlineMiddleware($request);
            return $next($request);
        });
    }

    protected function inlineMiddleware(Request $request = null)
    {
    }

    /**
     * @param array $payload
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    protected function response($payload, $status = Configuration::HTTP_RESPONSE_STATUS_OK, $headers = [])
    {
        return response()->json(
            $payload,
            ConfigHelper::getApiResponseStatus($status),
            ConfigHelper::getApiResponseHeaders($headers),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * @param array|null $data
     * @param array|string|null $message
     * @param array $headers
     * @return JsonResponse
     */
    protected function responseSuccess($data = null, $message = null, $headers = [])
    {
        $this->transactionComplete();
        return $this->response(static::successPayload($data, $message), Configuration::HTTP_RESPONSE_STATUS_OK, $headers);
    }

    /**
     * @param Exception|array|string|null $message
     * @param array|null $data
     * @param array $headers
     * @return JsonResponse
     */
    protected function responseFail($message = null, $data = null, $headers = [])
    {
        $this->transactionStop();
        if ($message instanceof BaseException) {
            LogHelper::error($message);
        }
        return $this->response(static::failPayload($data, $message), Configuration::HTTP_RESPONSE_STATUS_ERROR, $headers);
    }

    protected function getRespondedDataWithKey($data, $key = null)
    {
        return is_null($key) ? $data : [
            $key => $data,
        ];
    }
}