<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

use Psr\Http\Message\RequestInterface;

class SignatureHelper
{
    public static function createBeSignedString($appId, $timestamp, $nonce, RequestInterface $request): string
    {
        $beSigned = [];
        $beSigned['app_id'] = $appId;
        $beSigned['timestamp'] = $timestamp;
        $beSigned['nonce'] = $nonce;
        $beSigned['path'] = static::getPath($request);
        $beSigned['method'] = $request->getMethod();
        ksort($beSigned);
        return http_build_query($beSigned);
    }

    public static function getPath(RequestInterface $request): string
    {
        $path = rtrim($request->getUri()->getPath(), '/');
        if (\strpos($path, '/') !== 0) {
            $path = '/' . $path;
        }
        return $path;
    }
}
