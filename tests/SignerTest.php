<?php

declare(strict_types=1);

namespace Tests;

use Kaxiluo\ApiSignature\Constant\ValidationField;
use Kaxiluo\ApiSignature\Client\RequestSigner;

class SignerTest extends TestCase
{
    public function testSigner()
    {
        $requestSigner = new RequestSigner('1', 'iamaappsecret');

        $signedRequest = $requestSigner->sign($this->createRequest());

        $this->assertTrue($signedRequest->hasHeader($requestSigner->getHeaderName()));
        $signedValue = $signedRequest->getHeaderLine($requestSigner->getHeaderName());
        $this->assertStringContainsString(ValidationField::APP_ID . '=1', $signedValue);
        $this->assertStringContainsString(ValidationField::TIMESTAMP, $signedValue);
        $this->assertStringContainsString(ValidationField::NONCE, $signedValue);
        $this->assertStringContainsString(ValidationField::SIGNATURE, $signedValue);
    }
}
