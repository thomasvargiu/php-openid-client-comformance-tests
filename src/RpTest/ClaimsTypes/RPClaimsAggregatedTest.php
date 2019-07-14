<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ClaimsTypes;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\Claims\AggregateParser;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Make a UserInfo Request and read the Aggregated Claims.
 *
 * Understand the aggregated claims in the UserInfo Response.
 */
class RPClaimsAggregatedTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-claims-aggregated';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();
        $client = $this->registerClient($testInfo);

        $authorizationService = $container->get(AuthorizationService::class);
        $userInfoService = $container->get(UserinfoService::class);
        $aggregatedClaims = $container->get(AggregateParser::class);

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
        $aggregated  = $aggregatedClaims->unpack($client, $userInfo);

        Assert::assertArrayHasKey('shoe_size', $aggregated);
        Assert::assertArrayHasKey('eye_color', $aggregated);

        Assert::assertSame(8, $aggregated['shoe_size']);
        Assert::assertSame('blue', $aggregated['eye_color']);
    }
}
