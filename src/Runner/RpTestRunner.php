<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\Runner;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\IncompleteTestError;
use Throwable;
use TMV\OpenIdClient\ConformanceTest\Provider\ImplementationProvider;
use TMV\OpenIdClient\ConformanceTest\RpTest\RpTestInterface;
use TMV\OpenIdClient\ConformanceTest\TestInfo;

class RpTestRunner
{
    /** @var ImplementationProvider */
    private $implementationProvider;

    /**
     * RpTestRunner constructor.
     * @param ImplementationProvider $implementationProvider
     */
    public function __construct(ImplementationProvider $implementationProvider)
    {
        $this->implementationProvider = $implementationProvider;
    }

    public function run(RpTestInterface $test, TestInfo $testInfo)
    {
        $testResult = new RpTestResult(
            $test,
            $testInfo,
            $this->implementationProvider->getCallableCode([$test, 'execute'])
        );

        try {
            Assert::resetCount();
            $test->execute($testInfo);

            if (0 === Assert::getCount()) {
                throw new IncompleteTestError('There was no assertions in test');
            }
        } catch (Throwable $e) {
            $testResult->setException($e);
        }

        return $testResult;
    }
}
