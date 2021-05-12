<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

use Kaxiluo\ApiSignature\Constant\ValidationField;
use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Psr\Http\Message\RequestInterface;
use Psr\SimpleCache\CacheInterface;

class SignatureValidator
{
    private $cache;

    private $appSecretFinder;

    private $headerName;

    private $lifetime;

    private $nonceCacheKeyPrefix;

    public function __construct(
        CacheInterface $cache,
        callable $appSecretFinder,
        $headerName = 'X-Signature',
        $lifetime = 300,
        $nonceCacheKeyPrefix = 'api:nonce:'
    ) {
        $this->cache = $cache;
        $this->appSecretFinder = $appSecretFinder;

        $this->setHeaderName($headerName);
        $this->setLifetime($lifetime);
        $this->setNonceCacheKeyPrefix($nonceCacheKeyPrefix);
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws InvalidSignatureException
     */
    public function verify(RequestInterface $request): bool
    {
        $originalHeader = $request->getHeaderLine($this->getHeaderName());
        if (empty($originalHeader)) {
            throw new InvalidSignatureException(ValidationField::APP_ID, 'signature header required.');
        }

        parse_str($originalHeader, $params);

        $appId = $params[ValidationField::APP_ID] ?? '';
        $appSecret = $this->validateAppId($appId);

        $timestamp = $params[ValidationField::TIMESTAMP] ?? '';
        $this->validateTimestamp($timestamp);

        $nonce = $params[ValidationField::NONCE] ?? '';
        $this->validateNonce($nonce);

        $signature = $params[ValidationField::SIGNATURE] ?? '';

        $beSignedString = SignatureHelper::createBeSignedString($appId, $timestamp, $nonce, $request);

        $this->validateSignature($beSignedString, $signature, $appSecret);

        $this->setNonceCache($nonce);

        return true;
    }

    private function validateAppId($appId): string
    {
        if (empty($appId)) {
            throw new InvalidSignatureException(ValidationField::APP_ID, 'app id required.');
        }

        $appSecret = call_user_func($this->appSecretFinder, $appId);

        if (empty($appSecret)) {
            throw new InvalidSignatureException(ValidationField::APP_ID, 'invalid app id.');
        }

        return $appSecret;
    }

    private function validateTimestamp($time)
    {
        $time = intval($time);
        $now = time();

        if ($time > $now || $now - $time > $this->getLifetime()) {
            throw new InvalidSignatureException(ValidationField::TIMESTAMP, 'invalid timestamp');
        }
    }

    private function validateNonce($nonce)
    {
        if (empty($nonce) || $this->cache->has($this->getNonceCacheKey($nonce))) {
            throw new InvalidSignatureException(ValidationField::NONCE, 'invalid nonce.');
        }
    }

    private function validateSignature($beSignedString, $signature, $appSecret)
    {
        if (empty($signature) || !SignatureUtil::verify($beSignedString, $signature, $appSecret)) {
            throw new InvalidSignatureException(ValidationField::SIGNATURE, 'invalid signature.');
        }
    }

    private function getNonceCacheKey($nonce): string
    {
        return $this->getNonceCacheKeyPrefix() . $nonce;
    }

    private function setNonceCache($nonce)
    {
        $duration = 'PT' . $this->getLifetime() . 'S';
        $this->cache->set($this->getNonceCacheKey($nonce), 1, new \DateInterval($duration));
    }

    public function getHeaderName()
    {
        return $this->headerName;
    }

    protected function setHeaderName($headerName)
    {
        $this->headerName = $headerName;
    }

    public function getLifetime(): int
    {
        return (int)$this->lifetime;
    }

    protected function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    public function getNonceCacheKeyPrefix()
    {
        return $this->nonceCacheKeyPrefix;
    }

    public function setNonceCacheKeyPrefix($nonceCacheKeyPrefix)
    {
        $this->nonceCacheKeyPrefix = $nonceCacheKeyPrefix;
    }
}
