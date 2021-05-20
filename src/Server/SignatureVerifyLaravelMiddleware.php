<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Server;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class SignatureVerifyLaravelMiddleware extends SignatureVerifyMiddleware
{
    protected function adaptRequest($request)
    {
        if ($request instanceof Request) {
            $psr17Factory = new Psr17Factory();
            $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
            $request = $psrHttpFactory->createRequest($request);
        }

        return $request;
    }

    protected function handleNext($request, $handler)
    {
        return $handler($request);
    }

    protected function handleInvalidSignature(InvalidSignatureException $exception)
    {
        return new JsonResponse(['error' => $exception->getMessage()], 401);
    }
}
