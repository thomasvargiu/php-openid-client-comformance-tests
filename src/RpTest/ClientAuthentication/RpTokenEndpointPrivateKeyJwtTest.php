<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ClientAuthentication;

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Use the 'private_key_jwt' method to authenticate at the Authorization Server when using the token endpoint.
 *
 * A Token Response, containing an ID token.
 */
class RpTokenEndpointPrivateKeyJwtTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-token_endpoint-private_key_jwt';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $jwk = JWKFactory::createRSAKey(2048, ['use' => 'sig', 'alg' => 'RS256']);
        $jwks = new JWKSet([$jwk]);
        $publicJwks = new JWKSet([$jwk->toPublic()]);

        $client = $this->registerClient($testInfo, [
            'token_endpoint_auth_method' => 'private_key_jwt',
            'jwks' => $publicJwks->jsonSerialize(),
        ], $jwks);

        $authorizationService = $container->get(AuthorizationService::class);

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
