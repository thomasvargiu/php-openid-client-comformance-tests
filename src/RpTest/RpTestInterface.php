<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\RpTest;

use TMV\OpenIdClient\ConformanceTest\TestInfo;

interface RpTestInterface
{
    public function getTestId(): string;

    public function execute(TestInfo $testInfo): void;
}
