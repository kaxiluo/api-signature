<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Server;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;

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

        return $this->handleNext($request, $handler);
    }

    abstract protected function handleNext($request, $handler);

    protected function adaptRequest($request)
    {
        return $request;
    }

    abstract protected function handleInvalidSignature(InvalidSignatureException $exception);

    abstract protected function getCacheProvider();

    abstract protected function getAppSecretByAppId($appId): string;
}
