<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest;

use function array_merge;
use Http\Client\Common\HttpMethodsClientInterface;
use Jose\Component\Core\JWKSet;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use TMV\OpenIdClient\Client\Client;
use TMV\OpenIdClient\Client\ClientInterface;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Exception\OAuth2Exception;
use TMV\OpenIdClient\Exception\RemoteException;
use TMV\OpenIdClient\Issuer\IssuerFactory;
use TMV\OpenIdClient\Client\Metadata\ClientMetadata;
use TMV\OpenIdClient\Service\ClientRegistrationService;
use Zend\Diactoros\ServerRequestFactory;

abstract class AbstractRpTest implements RpTestInterface
{
    protected const REDIRECT_URI = 'https://rp.test/callback';

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function registerClient(TestInfo $testInfo, array $metadata = [], JWKSet $jwks = null): ClientInterface
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);
        $registrationService = $container->get(ClientRegistrationService::class);
        try {
            $issuer = $issuerFactory->fromUri($testInfo->getRpUri() . '/' . $this->getTestId() . '/.well-known/openid-configuration');
        } catch (OAuth2Exception $e) {
            echo sprintf('%s (%s)', $e->getMessage(), $e->getDescription()) . PHP_EOL;
            throw $e;
        } catch (RemoteException $e) {
            echo $e->getResponse()->getBody() . PHP_EOL;
            throw $e;
        }

        $metadata = array_merge([
            'client_name' => 'php-openid-client/v1.x (https://github.com/thomasvargiu/php-openid-client)',
            'redirect_uris' => [static::REDIRECT_URI],
            'contacts' => [
                'tvargiu@gmail.com',
            ],
            'grant_types' => [
                'authorization_code',
                'implicit',
            ],
            'response_types' => [
                $testInfo->getResponseType(),
            ],
        ], $metadata);

        try {
            $clientMetadata = ClientMetadata::fromArray($registrationService->register($issuer, $metadata));
        } catch (OAuth2Exception $e) {
            echo sprintf('%s (%s)', $e->getMessage(), $e->getDescription()) . PHP_EOL;
            throw $e;
        } catch (RemoteException $e) {
            echo $e->getResponse()->getBody() . PHP_EOL;
            throw $e;
        }

        return new Client($issuer, $clientMetadata, $jwks ?: new JWKSet([]));
    }

    protected function getHttpClient(): HttpMethodsClientInterface
    {
        /** @var HttpMethodsClientInterface $httpClient */
        $httpClient = $this->getContainer()->get('httplug.clients.default.http_methods');

        return $httpClient;
    }

    protected function simulateAuthRedirect(string $uri, string $accept = 'application/json'): ServerRequestInterface
    {
        $response = $this->getHttpClient()->get($uri, ['accept' => $accept]);

        $serverRequestFactory = new ServerRequestFactory();

        /** @var string $location */
        $location = $response->getHeader('location')[0] ?? null;

        return $serverRequestFactory->createServerRequest('GET', $location);
    }
}
