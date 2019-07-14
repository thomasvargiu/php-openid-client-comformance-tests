<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\NonceRequestParameter;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Exception\InvalidArgumentException;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Always send a 'nonce' value as a request parameter while using implicit or hybrid flow.
 * Verify the 'nonce' value returned in the ID Token.
 *
 * An ID Token, either from the Authorization Endpoint or from the Token Endpoint, containing the same 'nonce' value
 * as passed in the authentication request when using hybrid flow or implicit flow.
 */
class RpNonceUnlessCodeFlowTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-nonce-unless-code-flow';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo);

        $authorizationService = $container->get(AuthorizationService::class);

        try {
            $authorizationService->getAuthorizationUri($client, [
                'response_type' => $testInfo->getResponseType(),
            ]);

            throw new AssertionFailedError('No assertion');
        } catch (InvalidArgumentException $e) {
            Assert::assertRegExp('/nonce MUST be provided for implicit and hybrid flows/', $e->getMessage());
        }

        $nonce = \bin2hex(\random_bytes(32));
        $authSession = AuthSession::fromArray(['nonce' => $nonce]);

        $uri = $authorizationService->getAuthorizationUri($client, [
            'response_type' => $testInfo->getResponseType(),
            'nonce' => $nonce,
        ]);
        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);
        $tokenSet = $authorizationService->callback($client, $params, null, $authSession);

        Assert::assertTrue(true);
    }
}
