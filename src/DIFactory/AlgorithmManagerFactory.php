<?php

declare(strict_types=1);

namespace TMV\OpenIdClient\ConformanceTest\DIFactory;

use Jose\Component\Core\AlgorithmManager;
use Psr\Container\ContainerInterface;

class AlgorithmManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $factory = $container->get(\Jose\Component\Core\AlgorithmManagerFactory::class);
        return new AlgorithmManager($factory->all());
    }
}
