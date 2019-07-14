<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\IdToken;

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Request an signed ID Token. Verify the signature on the ID Token using the keys published by the Issuer.
 *
 * Accept the ID Token after doing ID Token validation.
 */
class RpIdTokenSigEncA128KWTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-id_token-sig+enc-a128kw';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo, [
            'id_token_signed_response_alg' => 'RS256',
            'id_token_encrypted_response_alg' => 'A128KW',
            'id_token_encrypted_response_enc' => 'A256CBC-HS512',
        ]);

        Assert::assertSame('RS256', $client->getMetadata()->get('id_token_signed_response_alg'));
        Assert::assertSame('A128KW', $client->getMetadata()->get('id_token_encrypted_response_alg'));
        Assert::assertSame('A256CBC-HS512', $client->getMetadata()->get('id_token_encrypted_response_enc'));

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
        Assert::arrayHasKey('email', $tokenSet->claims());
    }
}
