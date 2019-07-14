<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest;

use Http\Client\Common\HttpMethodsClientInterface;

class RpTestUtil
{
    /** @var HttpMethodsClientInterface */
    private $client;
    /** @var string */
    private $logDir;

    /**
     * RpTestUtil constructor.
     * @param HttpMethodsClientInterface $client
     * @param string $logDir
     */
    public function __construct(
        HttpMethodsClientInterface $client,
        string $logDir = __DIR__ . '/../log'
    )
    {
        $this->client = $client;
        $this->logDir = $logDir;
    }

    private function mkdir(string $dirname): void
    {
        if (! \file_exists($dirname) && ! mkdir($concurrentDirectory = $dirname, 0777, true) && ! is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    public function clearLogs(string $root, string $rpId): void
    {
        $response = $this->client->get($root . '/log/' . $rpId);

        if (! \preg_match('/Clear all test logs/', (string) $response->getBody())) {
            return;
        }

        $this->client->get($root . '/clear/' . $rpId);
    }

    public function downloadLogs(TestInfo $testInfo, string $profile, string $responseType): void
    {
        $response = $this->client->get($testInfo->getRpLogsUri());

        if (! \preg_match('/Download tar file/', (string) $response->getBody())) {
            return;
        }

        $logDir = $this->logDir . '/' . ltrim($profile, '@');
        $this->mkdir($logDir);

        $response = $this->client->get($testInfo->getRoot() . '/mktar/' . $testInfo->getRpUri());
        $handle = \fopen($logDir . '/log-' . $responseType . '.tar', 'wb+');

        while (! $response->getBody()->eof()) {
            \fwrite($handle, $response->getBody()->getContents());
        }
        \fclose($handle);
    }
}
