<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature\Client;

use Kaxiluo\ApiSignature\Constant\ValidationField;
use Kaxiluo\ApiSignature\SignatureHelper;
use Kaxiluo\ApiSignature\SignatureUtil;
use Psr\Http\Message\RequestInterface;

class RequestSigner
{
    private $appId;

    private $appSecret;

    private $headerName;

    public function __construct($appId, $appSecret, $headerName = 'X-Signature')
    {
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);
        $this->setHeaderName($headerName);
    }

    public function sign(RequestInterface $request): RequestInterface
    {
        $data = [];
        $data[ValidationField::APP_ID] = $this->getAppId();
        $data[ValidationField::TIMESTAMP] = time();
        $data[ValidationField::NONCE] = uniqid();

        $beSignedString = SignatureHelper::createBeSignedString(
            $data[ValidationField::APP_ID],
            $data[ValidationField::TIMESTAMP],
            $data[ValidationField::NONCE],
            $request
        );
        $data[ValidationField::SIGNATURE] = SignatureUtil::sign($beSignedString, $this->getAppSecret());

        return $request->withHeader($this->getHeaderName(), http_build_query($data));
    }

    public function getAppId()
    {
        return $this->appId;
    }

    protected function setAppId($appId)
    {
        $this->appId = $appId;
    }

    public function getAppSecret()
    {
        return $this->appSecret;
    }

    protected function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }

    public function getHeaderName()
    {
        return $this->headerName;
    }

    protected function setHeaderName($headerName)
    {
        $this->headerName = $headerName;
    }
}
