<?php

// Basic configuration, used in web and console applications
return [
    'bootstrap' => [
        'gii',
    ],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
            'layout' => '@app/views/layouts/main',
            'allowedIPs' => [
                '127.0.0.1',
                '::1',
                '192.168.*',
            ],
            'generators' => [
                'giiant-model' => [
                    'class' => 'schmunk42\giiant\generators\model\Generator',
                    'templates' => [
                        'console' => '@app/giiant/model/console',
                    ],
                ],
                'giiant-crud' => [
                    'class' => 'schmunk42\giiant\generators\crud\Generator',
                    'templates' => [
                        'console' => '@app/giiant/crud/console',
                    ],
                ],
            ],
        ],
    ],
];
