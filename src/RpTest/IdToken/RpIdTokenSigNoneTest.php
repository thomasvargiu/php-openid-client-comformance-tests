<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Use Code Flow and retrieve an unsigned ID Token.
 * This test is only applicable when response_type='code'.
 *
 * Accept the ID Token after doing ID Token validation.
 */
class RpIdTokenSigNoneTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-sig-none';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo, ['id_token_signed_response_alg' => 'none']);

        Assert::assertSame('none', $client->getMetadata()->get('id_token_signed_response_alg'));

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
