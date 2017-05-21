<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'settings' => [
            'class' => 'yii2mod\settings\components\Settings',
        ],
        'i18n' => [
            'translations' => [
                'yii2mod.settings' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2mod/settings/messages',
                ],
            ]
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['currencies'],
                    'logFile' => '@runtime/logs/currencies-update.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['rates'],
                    'logFile' => '@runtime/logs/rates-update.log'
                ],
                [
                    'class' => 'yii\log\EmailTarget',
                    'categories' => ['currencies', 'rates'],
                    'levels' => ['error'],
                    'message' => [
                        'to' => ['admin@currencies.localhost'],
                        'subject' => 'Bank requests errors',
                    ],
                ]
            ],
        ],
    ],
    'aliases' => [
        '@api' => '@common/../api'
    ]
];
