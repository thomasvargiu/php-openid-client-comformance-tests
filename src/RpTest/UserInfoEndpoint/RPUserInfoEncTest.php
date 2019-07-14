<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\UserInfoEndpoint;

use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use TMV\OpenIdClient\Service\UserinfoService;

/**
 * Request encrypted UserInfo. Decrypt the UserInfo Response.
 *
 * A UserInfo Response.
 */
class RPUserInfoEncTest extends AbstractRpTest
{
    public function getTestId(): string
    {
        return 'rp-userinfo-enc';
    }

    public function execute(TestInfo $testInfo): void
    {
        $jwkEncAlg = JWKFactory::createRSAKey(2048, ['alg' => 'RSA1_5', 'use' => 'enc']);

        $jwks = new JWKSet([$jwkEncAlg]);
        $publicJwks = new JWKSet(\array_map(static function (JWK $jwk) {
            return $jwk->toPublic();
        }, $jwks->all()));

        $client = $this->registerClient($testInfo, [
            'userinfo_signed_response_alg' => 'none',
            'userinfo_encrypted_response_alg' => 'RSA1_5',
            'jwks' => $publicJwks->jsonSerialize(),
        ], $jwks);

        Assert::assertSame('none', $client->getMetadata()->get('userinfo_signed_response_alg'));
        Assert::assertSame('RSA1_5', $client->getMetadata()->get('userinfo_encrypted_response_alg'));

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
