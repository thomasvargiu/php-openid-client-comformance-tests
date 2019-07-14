<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\UserInfoEndpoint;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Make a UserInfo Request and verify the 'sub' value of the UserInfo Response by comparing it with the ID Token's 'sub' value.
 *
 * Identify the invalid 'sub' value and reject the UserInfo Response.
 */
class RpUserInfoBadSubClaimTest extends AbstractRpTest
{
    public function getTestId(): string
    {
        return 'rp-userinfo-bad-sub-claim';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo);

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

        try {
            $userInfoService->getUserInfo($client, $tokenSet);
            throw new AssertionFailedError('No assertions');
        } catch (\Throwable $e) {
            Assert::assertRegExp('/Userinfo sub mismatch/', $e->getMessage());
        }
    }
}
