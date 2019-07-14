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
 * Request signed UserInfo.
 *
 * Successful signature verification of the UserInfo Response.
 */
class RPUserInfoSigTest extends AbstractRpTest
{
    public function getTestId(): string
    {
        return 'rp-userinfo-sig';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo, [
            'userinfo_signed_response_alg' => 'RS256',
        ]);

        Assert::assertSame('RS256', $client->getMetadata()->get('userinfo_signed_response_alg'));

        // Get authorization redirect uri
        $authorizationService = $this->getContainer()->get(AuthorizationService::class);
        $userInfoService = $this->getContainer()->get(UserinfoService::class);

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

        $userInfo = $userInfoService->getUserInfo($client, $tokenSet);

        Assert::assertArrayHasKey('sub', $userInfo);
    }
}
