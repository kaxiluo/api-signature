<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

abstract class SignatureVerifyMiddleware implements MiddlewareInterface
{
    protected $headerName = 'X-Signature';

    protected $lifetime = 300;

    protected $nonceCacheKeyPrefix = 'api:nonce:';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            (new SignatureValidator(
                $this->getCacheProvider(),
                [$this, 'getAppSecretByAppId'],
                $this->headerName,
                $this->lifetime,
                $this->nonceCacheKeyPrefix
            ))->verify($request);
        } catch (InvalidSignatureException $exception) {
            $res = $this->handleInvalidSignature($exception);
            if ($res instanceof ResponseInterface) {
                return $res;
            }
        }

        return $handler->handle($request);
    }

    abstract protected function handleInvalidSignature(InvalidSignatureException $exception): ResponseInterface;

    abstract protected function getCacheProvider(): CacheInterface;

    abstract protected function getAppSecretByAppId($appId): string;
}
