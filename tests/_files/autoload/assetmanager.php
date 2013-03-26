<?php

return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                'static' => __DIR__ . '/../assets',
            )
        ),
        'cache_control' => array(
            'lifetime' => '5m'
        )
    )
);
