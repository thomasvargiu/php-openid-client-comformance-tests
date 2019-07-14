<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ClaimsTypes;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\Claims\DistributedParser;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Make a UserInfo Request and read the Distributed Claims.
 *
 * Understand the distributed claims in the UserInfo Response.
 */
class RPClaimsDistributedTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-claims-distributed';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo);

        $authorizationService = $container->get(AuthorizationService::class);
        $userInfoService = $container->get(UserinfoService::class);
        $distributedClaims = $container->get(DistributedParser::class);

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
        $distributed = $distributedClaims->fetch($client, $userInfo);

        Assert::assertArrayHasKey('age', $distributed);
        Assert::assertSame(30, $distributed['age']);
    }
}
