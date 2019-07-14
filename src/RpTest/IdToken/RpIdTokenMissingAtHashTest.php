<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Make an authentication request using response_type='id_token token' for Implicit Flow or
 * response_type='code id_token token' for Hybrid Flow. Verify the 'at_hash' presence in the returned ID Token.
 *
 * Identify missing 'at_hash' value and reject the ID Token.
 */
class RpIdTokenMissingAtHashTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-missing-at_hash';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo);

        // Get authorization redirect uri
        $authorizationService = $this->getContainer()->get(AuthorizationService::class);

        $authSession = AuthSession::fromArray([
            'nonce' => \bin2hex(\random_bytes(32)),
        ]);

        $uri = $authorizationService->getAuthorizationUri($client, [
            'response_type' => $testInfo->getResponseType(),
            'nonce' => $authSession->getNonce(),
        ]);

        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);

        try {
            $authorizationService->callback($client, $params, null, $authSession);
            throw new AssertionFailedError('No assertion');
        } catch (\Throwable $e) {
            Assert::assertRegExp('/The following claims are mandatory: at_hash/', $e->getMessage());
        }
    }
}
