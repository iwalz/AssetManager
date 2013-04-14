<?php
return array(
    'service_manager' => array (
        'factories' => array (
            'AssetManager\Service\AssetManager'                 => 'AssetManager\Service\AssetManagerServiceFactory',
            'AssetManager\Service\AssetFilterManager'           => 'AssetManager\Service\AssetFilterManagerServiceFactory',
            'AssetManager\Service\AssetCacheManager'            => 'AssetManager\Service\AssetCacheManagerServiceFactory',
            'AssetManager\Service\AggregateResolver'            => 'AssetManager\Service\AggregateResolverServiceFactory',
            'AssetManager\CacheControl\CacheController'              => 'AssetManager\CacheControl\CacheControllerServiceFactory',
            'AssetManager\CacheBusting\AssetCacheBustingManager'     => 'AssetManager\CacheBusting\AssetCacheBustingManagerServiceFactory',
            'AssetManager\Resolver\MapResolver'                 => 'AssetManager\Service\MapResolverServiceFactory',
            'AssetManager\Resolver\PathStackResolver'           => 'AssetManager\Service\PathStackResolverServiceFactory',
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 'AssetManager\Service\PrioritizedPathsResolverServiceFactory',
            'AssetManager\Resolver\CollectionResolver'          => 'AssetManager\Service\CollectionResolverServiceFactory',
            'AssetManager\CacheBusting\Cache'                   => 'AssetManager\CacheBusting\CacheFactory'
        ),
        'invokables' => array(
            'mime_resolver' => 'AssetManager\Service\MimeResolver',
        ),
        'abstract_factories' => array(
            'AssetManager\Config\AbstractConfigFactory'
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'headlink' => 'AssetManager\Helper\HeadLinkServiceFactory',
            'headscript' => 'AssetManager\Helper\HeadScriptServiceFactory',
        ),
    ),
    'asset_manager' => array(
        'resolvers' => array(
            'AssetManager\Resolver\MapResolver'                 => 2000,
            'AssetManager\Resolver\CollectionResolver'          => 1500,
            'AssetManager\Resolver\PrioritizedPathsResolver'    => 1000,
            'AssetManager\Resolver\PathStackResolver'           => 500,
        ),
        'cache_control' => array(
            'enabled' => false
        ),
        'cache_busting' => array(
            'enabled' => false,
            'cache' => 'Apc',
            'lifetime' => 15724800,
            'validation_lifetime' => 60
        )
    ),
);
