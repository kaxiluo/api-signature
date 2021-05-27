<?php

declare(strict_types=1);

namespace Tests;

use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Kaxiluo\ApiSignature\Client\RequestSigner;
use Kaxiluo\ApiSignature\Server\SignatureValidator;

class ValidatorTest extends TestCase
{
    public function testValidator()
    {
        $cache = $this->getMockCache();

        // validator
        $validator = new SignatureValidator($cache, function () {
            return 'iamaappsecret';
        });

        // create signed request
        $requestSigner = new RequestSigner('1', 'iamaappsecret');
        $signedRequest = $requestSigner->sign($this->createRequest());

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

    public function testValidatorOfPostRequest()
    {
        $cache = $this->getMockCache();

        // validator
        $validator = new SignatureValidator($cache, function () {
            return 'iamaappsecret';
        });

        // create signed request
        $requestSigner = new RequestSigner('1', 'iamaappsecret');
        $signedRequest = $requestSigner->sign($this->createRequest(
            ['foo' => 'bar'],
            ['X-foo' => 'bar'],
            'POST',
            'https://mock.mock/test?a=b&c= api &c'
        ));

        // verify success
        $cache->shouldReceive('has')->once()->andReturn(false);
        $res = $validator->verify($signedRequest);
        $this->assertTrue($res);
    }
}
