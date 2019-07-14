<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\Command;

use PHPUnit\Framework\TestResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TMV\OpenIdClient\ConformanceTest\Helper\RPLogsHelper;
use TMV\OpenIdClient\ConformanceTest\Provider\RpProfileTestsProvider;
use TMV\OpenIdClient\ConformanceTest\RpTest\RpTestInterface;
use TMV\OpenIdClient\ConformanceTest\Runner\RpTestResult;
use TMV\OpenIdClient\ConformanceTest\Runner\RpTestRunner;
use TMV\OpenIdClient\ConformanceTest\TestInfo;

class RpTest extends Command
{
    /** @var RpTestRunner */
    private $testRunner;
    /** @var RpProfileTestsProvider */
    private $testsProvider;
    /** @var RPLogsHelper */
    private $logsHelper;

    public function __construct(
        RpTestRunner $testRunner,
        RpProfileTestsProvider $testsProvider,
        RPLogsHelper $logsHelper
    )
    {
        $this->testRunner = $testRunner;
        $this->testsProvider = $testsProvider;
        $this->logsHelper = $logsHelper;

        parent::__construct('test');
    }

    protected function configure()
    {
        $this->setName('test')
            ->addOption('profile', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Profile', $this->testsProvider->getAvailableProfiles())
            ->addOption('test-id', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Filter test to execute')
            ->addOption('show-implementation', 'i', InputOption::VALUE_NONE, 'Whether to show implementation')
            ->addOption('show-environment', 'e', InputOption::VALUE_NONE, 'Whether to show environment')
            ->addOption('show-remote-logs', 'l', InputOption::VALUE_NONE, 'Whether to show remote logs')
            ->addOption('keep-logs', 'k', InputOption::VALUE_NONE, 'Whether to keep server logs')
            ->addOption('ignore-errors', null, InputOption::VALUE_NONE, 'Whether to stops on errors')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profiles = $input->getOption('profile');
        $testIds = $input->getOption('test-id');
        $showImplementation = (bool) $input->getOption('show-implementation');
        $showEnvironment = (bool) $input->getOption('show-environment');
        $showRemoteLogs = (bool) $input->getOption('show-remote-logs');
        $keepLogs = (bool) $input->getOption('keep-logs');
        $ignoreErrors = (bool) $input->getOption('ignore-errors');

        $errors = [];

        foreach ($profiles as $profile) {
            $tests = $this->testsProvider->getTests($profile);
            $responseType = $this->testsProvider->getResponseTypeForProfile($profile);

            $testInfo = new TestInfo($profile, $responseType);

            if (! $keepLogs) {
                $this->logsHelper->clearLogs($testInfo->getRoot(), $testInfo->getRpId());
            }

            if (count($testIds)) {
                $tests = \array_filter($tests, static function (RpTestInterface $test) use ($testIds) {
                    return \in_array($test->getTestId(), $testIds, true);
                });
            }

            foreach ($tests as $test) {
                $testName = $test->getTestId() . ' @' . $testInfo->getProfile();

                $startTime = new \DateTimeImmutable();
                $output->writeln("<comment>Test started at:</comment> <info>{$startTime->format(\DateTimeImmutable::RFC3339)}</info>", OutputInterface::VERBOSITY_DEBUG);
                $output->writeln('Executing test ' . $testName . '...', OutputInterface::VERBOSITY_DEBUG);

                $result = $this->testRunner->run($test, $testInfo);

                $output->writeln("<comment>Test:</comment> <info>$testName</info>", OutputInterface::VERBOSITY_NORMAL);

                if ($showEnvironment) {
                    $output->writeln('');
                    $this->printEnvironment($result, $output);
                }

                if ($showImplementation) {
                    $output->writeln('');
                    $this->printImplementation($result, $output);
                }


                if ($showRemoteLogs) {
                    $output->writeln('');
                    $this->printRemoteLog($result, $output);
                }

                if ($exception = $result->getException()) {
                    $output->writeln('<comment>Result:</comment> <error>Test failed!</error>', OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln((string) $exception, OutputInterface::VERBOSITY_DEBUG);
                } else {
                    $output->writeln('<comment>Result:</comment> <info>Test OK</info>', OutputInterface::VERBOSITY_NORMAL);
                }

                $this->printSeparator($output, OutputInterface::VERBOSITY_NORMAL);

                if ($result->getException()) {
                    $errors[] = $result;
                }

                if (! $ignoreErrors && $result->getException()) {
                    return 1;
                }
            }
        }

        if (count($errors)) {
            return 1;
        }
    }

    private function printSeparator(OutputInterface $output, int $options = 0): void
    {
        $output->writeln(\str_repeat('-', 80), $options);
    }

    private function printRemoteLog(RpTestResult $result, OutputInterface $output): void
    {
        $testInfo = $result->getTestInfo();
        $body = (string) $this->logsHelper->getLog(
            $testInfo->getRoot(),
            $testInfo->getRpId(),
            $result->getTest()->getTestId()
        )
            ->getBody();
        $output->writeln('<comment>Remote Log:</comment>');
        $output->writeln("<info>{$body}</info>");
    }

    private function printEnvironment(RpTestResult $result, OutputInterface $output): void
    {
        $testInfo = $result->getTestInfo();
        $output->writeln('<comment>Environment:</comment>');
        $output->writeln("<info>RP ID: {$testInfo->getRpId()}</info>");
        $output->writeln("<info>response_type: {$testInfo->getResponseType()}</info>");
    }

    private function printImplementation(RpTestResult $result, OutputInterface $output): void
    {
        $output->writeln('<comment>Implementation:</comment>');
        $output->writeln('');
        $output->writeln('<info>' . $result->getImplementation() . '</info>');
    }
}
