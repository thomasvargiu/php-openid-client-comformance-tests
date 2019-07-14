<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Request an ID token and verify its signature using the keys provided by the Issuer.
 *
 * Identify that the 'kid' value is missing from the JOSE header and that the Issuer publishes multiple keys in its
 * JWK Set document (referenced by 'jwks_uri'). The RP can do one of two things; reject the ID Token since it can not
 * by using the kid determined which key to use to verify the signature. Or it can just test all possible keys and hit
 * upon one that works, which it will in this case.
 */
class RpIdTokenKidAbsentMultipleJwksTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-kid-absent-multiple-jwks';
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
