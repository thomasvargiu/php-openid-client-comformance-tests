<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\ResponseTypeAndResponseMode;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Exception\OAuth2Exception;
use TMV\OpenIdClient\Model\AuthSession;
use TMV\OpenIdClient\Service\AuthorizationService;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Construct and send an Authentication Request with response mode set to form_post, max_age=0 and prompt=none which
 * results in the test suite returning an error because the requested conditions cannot be met.
 *
 * The HTML form post authorization error response is consumed, resulting in an error screen shown to the user.
 */
class RPResponseModeFormPostErrorTest extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-response_mode-form_post-error';
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
            'response_type' => $testInfo->getResponseType(),
            'response_mode' => 'form_post',
            'prompt' => 'none',
            'max_age' => 0,
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

        try {
            $authorizationService->getCallbackParams($serverRequest, $client);
            throw new AssertionFailedError('No assertions');
        } catch (OAuth2Exception $e) {
            Assert::assertSame('login_required', $e->getError());
        } catch (\Throwable $e) {
            throw new AssertionFailedError('No assertions');
        }
    }
}
