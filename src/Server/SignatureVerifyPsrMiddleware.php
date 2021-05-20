<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class SignatureVerifyPsrMiddleware extends SignatureVerifyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return call_user_func_array($this, [$request, $handler]);
    }

    protected function handleNext($request, $handler)
    {
        return $handler->handle($request);
    }
}
