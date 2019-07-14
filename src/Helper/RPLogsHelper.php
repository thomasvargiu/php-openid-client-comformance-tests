<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\Helper;

use Http\Client\Common\HttpMethodsClientInterface;
use Psr\Http\Message\ResponseInterface;

class RPLogsHelper
{
    /** @var HttpMethodsClientInterface */
    private $client;

    /**
     * RPLogsHelper constructor.
     * @param HttpMethodsClientInterface $client
     */
    public function __construct(HttpMethodsClientInterface $client)
    {
        $this->client = $client;
    }

    public function clearLogs(string $root, string $rpId): void
    {
        $response = $this->client->get($root . '/log/' . $rpId);

        if (! \preg_match('/Clear all test logs/', (string) $response->getBody())) {
            return;
        }

        $this->client->get($root . '/clear/' . $rpId);
    }

    public function getLog(string $root, string $rpId, string $testId): ?ResponseInterface
    {
        return $this->client->get($root . '/log/' . $rpId . '/' . $testId . '.txt');
    }

    public function downloadLogs(string $root, string $rpId): ?ResponseInterface
    {
        $response = $this->client->get($root . '/log/' . $rpId);

        if (! \preg_match('/Download tar file/', (string) $response->getBody())) {
            return null;
        }

        return $this->client->get($root . '/mktar/' . $rpId);
    }
}
