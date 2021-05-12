<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

use Psr\Http\Message\RequestInterface;

class RequestSignGuzzleMiddleware
{
    private $requestSigner;

    public function __construct(RequestSigner $requestSigner)
    {
        $this->requestSigner = $requestSigner;
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $handler($this->requestSigner->sign($request), $options);
        };
    }
}
