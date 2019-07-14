<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\UserInfoEndpoint;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Pass the access token as a form-encoded body parameter while doing the UserInfo Request.
 *
 * A UserInfo Response.
 */
class RpUserInfoBearerBodyTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-userinfo-bearer-body';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo);

        $authorizationService = $container->get(AuthorizationService::class);
        $userInfoService = $container->get(UserinfoService::class);

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
        $tokenSet = $authorizationService->callback($client, $params, null, $authSession);

        $userInfo = $userInfoService->getUserInfo($client, $tokenSet, true);

        Assert::assertArrayHasKey('sub', $userInfo);
    }
}
