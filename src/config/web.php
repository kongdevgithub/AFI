<?php

// Settings for web-application only
return [
    'on registerMenuItems' => function ($event) {
        Yii::$app->params['context.menuItems'] = \yii\helpers\ArrayHelper::merge(
            Yii::$app->params['context.menuItems'],
            $event->sender->getMenuItems()
        );
        $event->handled = true;
    },
    'components' => [
        'assetManager' => [
            'forceCopy' => false, // Note: May degrade performance with Docker or VMs
            'linkAssets' => YII_ENV_PROD ? false : true, // Note: May also publish files, which are excluded in an asset bundle
        ],
        'errorHandler' => [
            'class' => '\bedezign\yii2\audit\components\web\ErrorHandler',
            'errorAction' => 'error/index',
        ],
        'log' => [
            'targets' => [
                // writes to php-fpm output stream
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stdout',
                    'levels' => ['info', 'trace'],
                    'logVars' => [],
                    'enabled' => YII_DEBUG,
                ],
                [
                    'class' => 'codemix\streamlog\Target',
                    'url' => 'php://stderr',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
            ],
        ],
        'request' => [
            'cookieValidationKey' => getenv('APP_COOKIE_VALIDATION_KEY'),
        ],
        'session' => [
            'class' => 'yii\web\DbSession',
        ],
        'view' => [
            'class' => '\app\components\web\View',
        ]
    ],
    'modules' => [
        'gridview' => [
            'class' => 'kartik\grid\Module'
        ],
        'pages' => [
            'class' => 'dmstr\modules\pages\Module',
            'layout' => '@app/views/layouts/main',
        ],
        'settings' => [
            'class' => 'pheme\settings\Module',
            'layout' => '@app/views/layouts/box',
            'accessRoles' => ['settings-module'],
        ],
        'user' => [
            'class' => 'dektrium\user\Module',
            #'layout' => 'SEE_DEPENDENCY_INJECTION',
            'defaultRoute' => 'admin',
            'adminPermission' => 'user-module',
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enableFlashMessages' => false,
            'controllerMap' => [
                'admin' => 'app\controllers\user\AdminController',
                'security' => 'app\controllers\user\SecurityController',
                'settings' => 'app\controllers\user\SettingsController',
            ],
            'modelMap' => [
                'User' => 'app\models\User',
                'Profile' => 'app\models\Profile',
            ]
        ],
    ],
];
