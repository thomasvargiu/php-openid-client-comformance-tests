<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Request an ID token and verify its 'iss' value.
 *
 * Identify the incorrect 'iss' value and reject the ID Token after doing ID Token validation.
 */
class RpIdTokenIssuerMismatchTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-issuer-mismatch';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo);

        // Get authorization redirect uri
        $authorizationService = $this->getContainer()->get(AuthorizationService::class);
        $uri = $authorizationService->getAuthorizationUri($client, [
            'response_type' => $testInfo->getResponseType(),
            'nonce' => \bin2hex(\random_bytes(32)),
        ]);

        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);

        try {
            $authorizationService->callback($client, $params);
            throw new AssertionFailedError('No assertion');
        } catch (\Throwable $e) {
            Assert::assertRegExp('/Unknown issuer/', $e->getMessage());
        }
    }
}
