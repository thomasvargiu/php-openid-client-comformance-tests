<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ResponseTypeAndResponseMode;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

class RpResponseTypeCodeTest extends AbstractRpTest
{
    public function getTestId(): string
    {
        return 'rp-response_type-code';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo);

        Assert::assertSame('code', $testInfo->getResponseType());

        // Get authorization redirect uri
        $authorizationService = $this->getContainer()->get(AuthorizationService::class);
        $uri = $authorizationService->getAuthorizationUri($client, ['response_type' => $testInfo->getResponseType()]);
        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);
        Assert::assertArrayHasKey('code', $params);
    }
}
