<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest\Discovery;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use TMV\OpenIdClient\ConformanceTest\RpTest\AbstractRpTest;
use TMV\OpenIdClient\ConformanceTest\TestInfo;
use TMV\OpenIdClient\Issuer\IssuerFactory;

/**
 * Retrieve OpenID Provider Configuration Information for OpenID Provider from the .well-known/openid-configuration path.
 * Verify that the issuer in the provider configuration matches the one returned by WebFinger.
 *
 * Identify that the issuers are not matching and reject the provider configuration.
 */
class RPDiscoveryIssuerNotMatchingConfig extends AbstractRpTest
{

    public function getTestId(): string
    {
        return 'rp-discovery-issuer-not-matching-config';
    }

    public function execute(TestInfo $testInfo): void
    {
        $container = $this->getContainer();

        $issuerFactory = $container->get(IssuerFactory::class);

        $input = $testInfo->getRoot() . '/' . $testInfo->getRpId() . '/' . $this->getTestId() . '/joe';

        try {
            $issuerFactory->fromWebFinger($input);

            throw new AssertionFailedError('No assertions');
        } catch (\Throwable $e) {
            Assert::assertRegExp('/Discovered issuer mismatch/', $e->getMessage());
        }
    }
}
