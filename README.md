# API Signature, Verification

api signature and verification, easy to use it by middleware. or use it alone.

- Request signer
- Use request sign middleware to create guzzle client
- Signature validator
- General signature verify middleware
- Use signature verify middleware in laravel、hyperf ...

## Installing

Package is available on [Packagist](https://packagist.org/packages/kaxiluo/api-signature),

```bash
composer require kaxiluo/api-signature
```

## Usage

### Client (Request Sign)

```php
// use as guzzle client config
$config = [
    'base_uri' => 'https://yourserver.host',
    'verify' => false,
];
// create guzzle client with request sign middleware
$client = \Kaxiluo\ApiSignature\Client\GuzzleClientFactory::createClient('1', 'iamsecret', $config);
// enjoy..
$client->get('/test');
```

other, use it alone see:
`\Kaxiluo\ApiSignature\Client\RequestSigner`

### Server (Signature Verify)

using laravel middleware

```php
use Kaxiluo\ApiSignature\Server\SignatureVerifyLaravelMiddleware;

class MySignatureVerifyMiddleware extends SignatureVerifyLaravelMiddleware
{
    // custom signature header name. default is X-Signature
    protected $headerName = 'X-Your-Custom-Name';
    
    // nonce ttl. default is 300 s
    protected $lifetime = 500;

    protected function getAppSecretByAppId($appId): string
    {
        // TODO: Implement getAppSecretByAppId() method.
        // you can filter app_secret from config
        //return config('api.your-client.app_secret');
    }

    protected function getCacheProvider()
    {
        return app('cache.store');
    }
}
```

using hyperf middleware

```php
use Kaxiluo\ApiSignature\Exception\InvalidSignatureException;
use Kaxiluo\ApiSignature\Server\SignatureVerifyPsrMiddleware;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class MySignatureVerifyMiddleware extends SignatureVerifyPsrMiddleware
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function handleInvalidSignature(InvalidSignatureException $exception)
    {
        return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)
            ->json(['error' => $exception->getMessage()])
            ->withStatus(401);
    }

    protected function getCacheProvider(): CacheInterface
    {
        return $this->container->get(CacheInterface::class);
    }

    protected function getAppSecretByAppId($appId): string
    {
        // TODO: Implement getAppSecretByAppId() method.
        // you can filter app_secret from config
    }
}
```

other, use it alone see:
`\Kaxiluo\ApiSignature\Server\SignatureValidator`, `\Kaxiluo\ApiSignature\Server\SignatureVerifyMiddleware`
