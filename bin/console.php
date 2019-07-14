<?php

chdir(__DIR__);

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ .'/../config/container.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add($container->get(\TMV\OpenIdClient\ConformanceTest\Command\RpTest::class));

try {
    exit($application->run());
} catch (Throwable $e) {
    echo $e;
    exit(1);
}
