<?php

return [
    'cache'        => [
        'enabled'   => false,
        'directory' => __DIR__ . '/../cache'
    ],
    'namespaces'   => [
        'default' => [__DIR__ . '/../views/default'],
        'other'   => [__DIR__ . '/../views/other'],
    ],
    'dependencies' => [],
    'engines'      => []
];