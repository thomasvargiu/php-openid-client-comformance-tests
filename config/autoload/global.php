<?php

use Zend\Di;
use Zend\Di\Container;
use Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'dependencies' => [
        'abstract_factories' => [
            Container\ServiceManager\AutowireFactory::class
        ],
        'aliases' => [
            \Psr\Http\Message\ServerRequestInterface::class => \Zend\Diactoros\ServerRequestFactory::class,
            \Psr\Http\Message\RequestFactoryInterface::class => \Zend\Diactoros\RequestFactory::class,
            \Psr\Http\Message\ResponseFactoryInterface::class => \Zend\Diactoros\ResponseFactory::class,
            \Psr\Http\Message\UriFactoryInterface::class => \Zend\Diactoros\UriFactory::class,
            \Psr\Http\Message\StreamFactoryInterface::class => \Zend\Diactoros\StreamFactory::class,
            \Psr\Http\Client\ClientInterface::class => 'httplug.clients.default',

            \Jose\Component\Signature\Serializer\Serializer::class => \Jose\Component\Signature\Serializer\CompactSerializer::class,
            \Jose\Component\Encryption\Serializer\JWESerializer::class => \Jose\Component\Encryption\Serializer\CompactSerializer::class,

            \TMV\OpenIdClient\Token\TokenDecrypterInterface::class => \TMV\OpenIdClient\Token\TokenDecrypter::class,
            \TMV\OpenIdClient\Token\TokenSetVerifierInterface::class => \TMV\OpenIdClient\Token\TokenSetVerifier::class,
            \TMV\OpenIdClient\Token\IdTokenVerifierInterface::class => \TMV\OpenIdClient\Token\IdTokenVerifier::class,
            \TMV\OpenIdClient\Token\ResponseTokenVerifierInterface::class => \TMV\OpenIdClient\Token\ResponseTokenVerifier::class,
            \TMV\OpenIdClient\Issuer\Metadata\Provider\DiscoveryProviderInterface::class => \TMV\OpenIdClient\Issuer\Metadata\Provider\DiscoveryProvider::class,
            \TMV\OpenIdClient\Issuer\Metadata\Provider\WebFingerProviderInterface::class => \TMV\OpenIdClient\Issuer\Metadata\Provider\WebFingerProvider::class,
            \Http\Client\Common\HttpMethodsClientInterface::class => 'httplug.clients.default.http_methods',
        ],
        'factories' => [
            \Zend\Diactoros\ServerRequestFactory::class => InvokableFactory::class,
            \Zend\Diactoros\RequestFactory::class => InvokableFactory::class,
            \Zend\Diactoros\ResponseFactory::class => InvokableFactory::class,
            \Zend\Diactoros\UriFactory::class => InvokableFactory::class,
            \Zend\Diactoros\StreamFactory::class => InvokableFactory::class,

            \Jose\Component\Core\AlgorithmManager::class => \TMV\OpenIdClient\ConformanceTest\DIFactory\AlgorithmManagerFactory::class,

        ],
        'auto' => [
            'types' => [

            ],
        ],
    ],
];