<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase as BaseTestCase;

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

    public function createRequest(array $json = [], array $headers = []): Request
    {
        return new Request('GET', 'http://mock.mock/test', $headers, json_encode($json));
    }
}
