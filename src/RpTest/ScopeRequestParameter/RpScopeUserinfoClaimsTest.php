<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ScopeRequestParameter;

use PHPUnit\Framework\Assert;
use function TMV\OpenIdClient\base64url_decode;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

class RpScopeUserinfoClaimsTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-scope-userinfo-claims';
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
            'scope' => 'openid email',
            'response_type' => $testInfo->getResponseType(),
            'nonce' => $authSession->getNonce(),
        ]);

        // Simulate a redirect and create the server request
        $serverRequest = $this->simulateAuthRedirect($uri);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);

        $tokenSet = $authorizationService->callback($client, $params, null, $authSession);

        $accessToken = $tokenSet->getAccessToken();

        if ($accessToken) {
            $userInfo = $userInfoService->getUserInfo($client, $tokenSet);
        } else {
            $userInfo = \json_decode(base64url_decode(\explode('.', $tokenSet->getIdToken())[1]), true);
        }

        Assert::assertArrayHasKey('email', $userInfo);
    }
}
