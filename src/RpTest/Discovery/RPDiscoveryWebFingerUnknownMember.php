<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\Discovery;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;

/**
 * The webfinger response will contain a member that the client doesn't recognize.
 *
 * An issuer location should be returned.
 */
class RPDiscoveryWebFingerUnknownMember extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-discovery-webfinger-unknown-member';
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
