<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

class GuzzleClientFactory
{
    public static function createClient($appId, $appSecret, array $config = [], $headerName = 'X-Signature'): Client
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(new RequestSignGuzzleMiddleware(new RequestSigner($appId, $appSecret, $headerName)));
        $config = array_merge($config, ['handler' => $stack]);
        return new Client($config);
    }
}
