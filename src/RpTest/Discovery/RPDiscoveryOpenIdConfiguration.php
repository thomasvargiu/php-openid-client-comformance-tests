<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\Discovery;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;

/**
 * Retrieve and use the OpenID Provider Configuration Information.
 *
 * Read and use the JSON object returned from the OpenID Connect Provider.
 */
class RPDiscoveryOpenIdConfiguration extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-discovery-openid-configuration';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);

        $configUri = sprintf('%s/%s/%s/.well-known/openid-configuration', $testInfo->getRoot(), $testInfo->getRpId(), $this->getTestId());
        $issuer = $issuerFactory->fromUri($configUri);

        $expected = sprintf('%s/%s/%s', $testInfo->getRoot(), $testInfo->getRpId(), $this->getTestId());
        Assert::assertSame($expected, $issuer->getMetadata()->getIssuer());
    }
}
