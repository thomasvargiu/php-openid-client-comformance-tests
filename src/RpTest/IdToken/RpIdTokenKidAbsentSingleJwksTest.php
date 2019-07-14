<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Request an ID token and verify its signature using a single matching key provided by the Issuer.
 *
 * Use the single matching key out of the Issuer's published set to verify the ID Tokens signature
 * and accept the ID Token after doing ID Token validation.
 */
class RpIdTokenKidAbsentSingleJwksTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-kid-absent-single-jwks';
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

        $tokenSet = $authorizationService->callback($client, $params);

        Assert::assertNotNull($tokenSet->getIdToken());
    }
}
