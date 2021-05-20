<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Server;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class SignatureVerifyLaravelMiddleware extends SignatureVerifyMiddleware
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

    protected function getCacheProvider()
    {
        return $this->container->get('cache.store');
    }
}
