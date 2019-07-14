<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\Discovery;

use PHPUnit\Framework\Assert;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;

/**
 * Use WebFinger (RFC7033) and OpenID Provider Issuer Discovery to determine the location of the OpenID Provider.
 * The discovery should be done using URL syntax as user input identifier.
 *
 * An issuer location should be returned.
 */
class RPDiscoveryWebFingerUrl extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-discovery-webfinger-url';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);

        $input = $testInfo->getRoot() . '/' . $testInfo->getRpId() . '/' . $this->getTestId() . '/joe';
        $issuer = $issuerFactory->fromWebFinger($input);

        $expected = sprintf('%s/%s/%s', $testInfo->getRoot(), $testInfo->getRpId(), $this->getTestId());
        Assert::assertSame($expected, $issuer->getMetadata()->getIssuer());
    }
}
