<?php

$config = [
    'id' => 'testapp',
    'basePath' => realpath(__DIR__ . '/..'),
    'aliases' => [],
    'bootstrap' => [
        'fileProcessor',
    ],
    'modules' => [
        'fileProcessor' => [
            'class' => '\metalguardian\fileProcessor\Module',
        ],
    ],
    'components' => [
        'db' => [
            'class' => '\yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ],
    ],
];


if (is_file(__DIR__ . '/config.local.php')) {
    include(__DIR__ . '/config.local.php');
}

return $config;
