<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\Discovery;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;

/**
 * The Relying Party uses keys from the jwks_uri which has been obtained from the OpenID Provider Metadata.
 *
 * Should be able to verify signed responses and/or encrypt requests using obtained keys.
 */
class RPDiscoveryJwksUriKeys extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-discovery-jwks_uri-keys';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);

        $configUri = sprintf('%s/%s/%s/.well-known/openid-configuration', $testInfo->getRoot(), $testInfo->getRpId(), $this->getTestId());
        $issuer = $issuerFactory->fromUri($configUri);

        Assert::assertCount(4, $issuer->getJwks()->all());
    }
}
