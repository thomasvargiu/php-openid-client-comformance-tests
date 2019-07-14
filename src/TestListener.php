<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestListener as TestListenerInterface;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use TMV\OpenIdClient\ConformanceTest\RpTest\RpResponseTypeCodeTest;
use TMV\OpenIdClient\ConformanceTest\RpTest\RpTestInterface;

class TestListener implements TestListenerInterface
{
    use TestListenerDefaultImplementation;

    /** @var ContainerInterface */
    private $container;

    /** @var RpTestUtil */
    private $rpTestUtil;

    /** @var null|string */
    private $rpProfile;

    /** @var null|string */
    private $rpTestId;

    /**
     * TestListener constructor.
     */
    public function __construct()
    {
        $this->container = require __DIR__ . '/../config/container.php';
        $this->rpTestUtil = $this->container->get(RpTestUtil::class);
    }

    public function getRoot(): string
    {
        return 'https://rp.certification.openid.net:8080/';
    }

    public function getRpId(): string
    {
        return 'tmv_php-openid-client';
    }

    /**
     * A test suite started.
     */
    public function startTestSuite(TestSuite $suite): void
    {
        if (0 !== \strpos($suite->getName(), 'rp-')) {
            return;
        }

        $profile = \substr($suite->getName(), 3);

        $testMap = [
            'code-basic' => [
                'rp-response_type-code' => new RpResponseTypeCodeTest($this->container, new TestInfo($profile, 'code'))
            ]
        ];

        $this->rpTestUtil->clearLogs($this->getRoot(), $this->getRpId());

        $tests = $testMap[$profile] ?? [];

        foreach ($tests as $test) {
            $suite->addTest($test);
        }
    }

    /**
     * A test suite ended.
     */
    public function endTestSuite(TestSuite $suite): void
    {
        // TODO: Implement endTestSuite() method.
    }

    /**
     * A test started.
     */
    public function startTest(Test $test): void
    {
        if (! $test instanceof RpTestInterface) {
            return;
        }

    }

    /**
     * A test ended.
     */
    public function endTest(Test $test, float $time): void
    {
        if (! $test instanceof RpTestInterface) {
            return;
        }

        $testUtil = $this->container->get(RpTestUtil::class);

        $implementation = $this->getClosureDump([$test, 'execute']);
    }

    protected function getClosureDump(callable $closure, int $indent = 4) {
        if (\is_callable($closure)) {
            $closure = \Closure::fromCallable($closure);
        }

        $r = new ReflectionFunction($closure);
        $lines = \file($r->getFileName());
        $lines = \array_slice($lines, $r->getStartLine(), $r->getEndLine() - $r->getStartLine());
        if (\preg_match('/^ *{ *$/', $lines[0] ?? '')) {
            unset($lines[0]);
        }

        $firstLine = \array_shift($lines) ?: '';

        if (! \preg_match('/^ *{ *$/', $firstLine)) {
            \array_unshift($lines, $firstLine);
        }

        $lastLine = \array_pop($lines) ?: '';
        if (! \preg_match('/^ *} *$/', $lastLine)) {
            \array_push($lines, $lastLine);
        }

        // remove spaces based on first line
        if (\preg_match('/^( +)/', $lines[0] ?? '', $matches)) {
            $toTrim = \strlen($matches[1]);
            $lines = \array_map(static function (string $line) use ($toTrim) {
                return \preg_replace(sprintf('/^ {0,%d}/', $toTrim), '', $line);
            }, $lines);
        }

        if ($indent) {
            $lines = \array_map(static function (string $line) use ($indent) {
                return \str_repeat(' ', $indent) . $line;
            }, $lines);
        }

        return \implode('', $lines);
    }
}
