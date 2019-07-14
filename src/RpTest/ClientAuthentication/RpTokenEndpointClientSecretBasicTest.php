<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ClientAuthentication;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Service\AuthorizationService;

/**
 * Use the 'client_secret_basic' method to authenticate at the Authorization Server when using the token endpoint.
 *
 * A Token Response, containing an ID token.
 */
class RpTokenEndpointClientSecretBasicTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-token_endpoint-client_secret_basic';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo, [
            'token_endpoint_auth_method' => 'client_secret_basic',
        ]);

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
