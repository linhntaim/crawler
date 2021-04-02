<?php

namespace App\Http\Middleware;

use App\Http\Requests\Request;
use App\Utils\ConfigHelper;
use App\Utils\CryptoJs\AES;
use Closure;

class HeaderDecrypt
{
    public function handle(Request $request, Closure $next, $clientType = 'admin')
    {
        $clientConfig = ConfigHelper::getClient($clientType);
        if (!empty($clientConfig)) {
            $headers = ConfigHelper::get('headers');
            $headerEncryptExcepts = ConfigHelper::get('header_encrypt_excepts');
            if (isset($clientConfig['headers'])) {
                $headers = array_merge($headers, $clientConfig['headers']);
            }
            if (isset($clientConfig['header_encrypt_excepts'])) {
                $headerEncryptExcepts = array_merge($headerEncryptExcepts, $clientConfig['header_encrypt_excepts']);
            }
            $secret = (function ($secret) {
                $break64 = mb_strlen('base64:');
                if (mb_substr($secret, 0, $break64) == 'base64:') {
                    return base64_decode(mb_substr($secret, $break64));
                }
                return $secret;
            })($clientConfig['app_key']);
            foreach ($headers as $header) {
                if ($request->hasHeader($header)
                    && !in_array($header, $headerEncryptExcepts)
                    && !empty($headerValue = $request->header($header))) {
                    $request->headers->set($header, AES::decrypt(base64_decode($headerValue), $secret));
                }
            }
        }
        return $next($request);
    }
}
