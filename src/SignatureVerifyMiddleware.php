<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

abstract class SignatureVerifyMiddleware
{
    protected $headerName = 'X-Signature';

    protected $lifetime = 300;

    protected $nonceCacheKeyPrefix = 'api:nonce:';

    public function __invoke($request, $handler)
    {
        $adaptRequest = $this->adaptRequest($request);

        try {
            (new SignatureValidator(
                $this->getCacheProvider(),
                function ($appId) {
                    return $this->getAppSecretByAppId($appId);
                },
                $this->headerName,
                $this->lifetime,
                $this->nonceCacheKeyPrefix
            ))->verify($adaptRequest);
        } catch (InvalidSignatureException $exception) {
            return $this->handleInvalidSignature($exception);
        }

        return $handler($request);
    }

    private function adaptRequest($request)
    {
        if (!($request instanceof RequestInterface)) {
            $psr17Factory = new Psr17Factory();
            $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
            $request = $psrHttpFactory->createRequest($request);
        }

        return $request;
    }

    abstract protected function handleInvalidSignature(InvalidSignatureException $exception);

    abstract protected function getCacheProvider(): CacheInterface;

    abstract protected function getAppSecretByAppId($appId): string;
}
