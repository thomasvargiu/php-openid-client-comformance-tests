<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\DynamicClientRegistration;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;
use TMV\OpenIdClient\Service\ClientRegistrationService;

/**
 * Use the client registration endpoint in order to dynamically register the Relying Party.
 *
 * Get a Client Registration Response.
 */
class RPRegistrationDynamic extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-registration-dynamic';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);
        $clientRegistrationService = $container->get(ClientRegistrationService::class);

        $configUri = sprintf('%s/%s/%s/.well-known/openid-configuration', $testInfo->getRoot(), $testInfo->getRpId(), $this->getTestId());
        $issuer = $issuerFactory->fromUri($configUri);

        $metadata = $clientRegistrationService->register($issuer, [
            'client_name' => $testInfo->getRpId() . '/' . $this->getTestId(),
            'redirect_uris' => [
                'https://example.com/callback',
            ],
            'contacts' => [
                'foo@example.com',
            ],
        ]);

        Assert::assertArrayHasKey('client_id', $metadata);
        Assert::assertArrayHasKey('client_secret_expires_at', $metadata);
    }
}
