<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Mock\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function getMockery($class)
    {
        return \Mockery::mock($class);
    }

    public function createRequest(
        array $json = [],
        array $headers = [],
        $method = 'GET',
        $uri = 'http://mock.mock/test'
    ): Request
    {
        return new Request($method, $uri, $headers, json_encode($json));
    }

    public function getMockCache()
    {
        $cache = $this->getMockery(Cache::class);
        $cache->shouldReceive('set')->andReturn(false);
        return $cache;
    }
}
