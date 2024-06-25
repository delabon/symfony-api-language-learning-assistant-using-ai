<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class FakeHttpClient implements HttpClientInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param array<mixed> $options
     * @return ResponseInterface
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return new FakeHttpClientResponse();
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return new FakeHttpClientResponseStream();
    }

    /**
     * @param array<mixed> $options
     * @return $this
     */
    public function withOptions(array $options): static
    {
        return $this;
    }
}
