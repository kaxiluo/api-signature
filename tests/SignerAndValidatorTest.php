<?php

declare(strict_types=1);

namespace Tests;

use Kaxiluo\ApiSignature\Constant\ValidationField;
use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Kaxiluo\ApiSignature\Client\RequestSigner;
use Kaxiluo\ApiSignature\Server\SignatureValidator;
use Tests\Mock\Cache;

class SignerAndValidatorTest extends TestCase
{
    public function testSignerAndValidator()
    {
        // signer...
        $requestSigner = new RequestSigner('1', 'iamaappsecret');

        // signed request
        $signedRequest = $requestSigner->sign($this->createRequest());

        $this->assertTrue($signedRequest->hasHeader($requestSigner->getHeaderName()));
        $signedValue = $signedRequest->getHeaderLine($requestSigner->getHeaderName());
        $this->assertStringContainsString(ValidationField::APP_ID . '=1', $signedValue);
        $this->assertStringContainsString(ValidationField::TIMESTAMP, $signedValue);
        $this->assertStringContainsString(ValidationField::NONCE, $signedValue);
        $this->assertStringContainsString(ValidationField::SIGNATURE, $signedValue);

        // validator...
        $cache = $this->getMockCache();

        $validator = new SignatureValidator($cache, function () {
            return 'iamaappsecret';
        });

        // verify success
        $cache->shouldReceive('has')->once()->andReturn(false);
        $res = $validator->verify($signedRequest);
        $this->assertTrue($res);

        // invalid app id
        $this->expectException(InvalidSignatureException::class);
        $validator = new SignatureValidator($cache, function () {
            return '';
        });
        $validator->verify($signedRequest);
    }

    private function getMockCache()
    {
        $cache = $this->getMockery(Cache::class);
        $cache->shouldReceive('set')->andReturn(false);
        return $cache;
    }
}
