<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\NonceRequestParameter;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

class RpNonceInvalidTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-nonce-invalid';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo);

        $authorizationService = $container->get(AuthorizationService::class);
        $userInfoService = $container->get(UserinfoService::class);
        $nonce = \bin2hex(\random_bytes(32));
        $authSession = AuthSession::fromArray(['nonce' => $nonce]);

        $uri = $authorizationService->getAuthorizationUri($client, [
            'response_type' => $testInfo->getResponseType(),
            'nonce' => $nonce,
        ]);
        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        try {
            $params = $authorizationService->getCallbackParams($serverRequest, $client);
            $tokenSet = $authorizationService->callback($client, $params, null, $authSession);
            $accessToken = $tokenSet->getAccessToken();

            if ($accessToken) {
                $userInfoService->getUserInfo($client, $tokenSet);
            }

            throw new AssertionFailedError('No assertion');
        } catch (\Throwable $e) {
            Assert::assertRegExp('/Nonce mismatch.* got: 012345678/', $e->getMessage());
        }
    }
}
