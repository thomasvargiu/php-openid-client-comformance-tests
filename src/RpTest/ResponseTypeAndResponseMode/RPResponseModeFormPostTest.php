<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ResponseTypeAndResponseMode;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Make an authentication request with the response_type set to 'id_token token' and the response mode set to form_post.
 *
 * HTML form post response processed, resulting in query encoded parameters.
 */
class RPResponseModeFormPostTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-response_mode-form_post';
    }

    public function execute(TestInfo $testInfo): void
    {
        $client = $this->registerClient($testInfo);

        // Get authorization redirect uri
        $authorizationService = $this->getContainer()->get(AuthorizationService::class);

        $authSession = AuthSession::fromArray([
            'nonce' => \bin2hex(\random_bytes(32)),
        ]);

        $uri = $authorizationService->getAuthorizationUri($client, [
            'response_mode' => 'form_post',
            'nonce' => $authSession->getNonce(),
        ]);

        // Simulate a redirect and create the server request
        $serverRequestFactory = new ServerRequestFactory();
        $response = $this->getHttpClient()->get($uri);
        $body = (string) $response->getBody();

        \preg_match_all('/<input type="hidden" name="(\w+)" value="([^"]+)"\/>/', $body, $matches);
        $requestBody = \http_build_query(\array_combine($matches[1], $matches[2]));

        $serverRequest = $serverRequestFactory->createServerRequest('POST', 'http://redirect.dev', [
            'content-type' => 'application/x-www-form-urlencoded',
        ]);
        $serverRequest->getBody()->write($requestBody);

        $params = $authorizationService->getCallbackParams($serverRequest, $client);
        $tokenSet = $authorizationService->callback($client, $params, null, $authSession);

        Assert::assertNotNull($tokenSet->getIdToken());
    }
}
