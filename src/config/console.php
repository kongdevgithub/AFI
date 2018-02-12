<?php

// Settings for console-application only
return [
    'controllerNamespace' => 'app\commands',
    'components' => [
        'errorHandler' => [
            'class' => '\bedezign\yii2\audit\components\console\ErrorHandler',
        ],
        'urlManager' => [
            'hostInfo' => getenv('APP_HOST_INFO') ?: 'https://afi.ink',
            'baseUrl' => getenv('APP_BASE_URL') ?: '/',
        ],
    ],
    'controllerMap' => [
        'batch' => [
            'class' => '\schmunk42\giiant\commands\BatchController',
            'template' => 'console',
            'crudTemplate' => 'console',
            'modelDb' => 'dbData',
            'overwrite' => true,
            'interactive' => false,
            'modelNamespace' => 'app\models',
            'modelQueryNamespace' => 'app\models\query',
            'crudTidyOutput' => true,
            'crudAccessFilter' => true,
            'crudControllerNamespace' => 'app\controllers',
            'crudSearchModelNamespace' => 'app\models\search',
            'crudSearchModelSuffix' => 'Search',
            'crudViewPath' => '@app/views',
            'crudPathPrefix' => '//',
            'crudMessageCategory' => 'app',
            'tablePrefix' => '',
        ],
        'batch-crud' => [
            'class' => 'schmunk42\giiant\commands\BatchController',
            'modelDb' => 'dbData',
            'template' => 'console',
            'crudTemplate' => 'console',
            'overwrite' => true,
            'interactive' => false,
            'modelNamespace' => 'app\modules\crud\models',
            'modelQueryNamespace' => 'app\modules\crud\models\query',
            'crudTidyOutput' => true,
            'crudAccessFilter' => true,
            'crudControllerNamespace' => 'app\modules\crud\controllers',
            'crudSearchModelNamespace' => 'app\modules\crud\models\search',
            'crudSearchModelSuffix' => 'Search',
            'crudViewPath' => '@app/modules/crud/views',
            'crudPathPrefix' => '//crud/',
            'crudMessageCategory' => 'crud',
            'tablePrefix' => '',
        ],
        'batch-goldoc' => [
            'class' => '\schmunk42\giiant\commands\BatchController',
            'template' => 'console',
            'crudTemplate' => 'console',
            'modelDb' => 'dbGoldoc',
            'overwrite' => true,
            'interactive' => false,
            'modelNamespace' => 'app\modules\goldoc\models',
            'modelQueryNamespace' => 'app\modules\goldoc\models\query',
            'crudTidyOutput' => true,
            'crudAccessFilter' => true,
            'crudControllerNamespace' => 'app\modules\goldoc\controllers',
            'crudSearchModelNamespace' => 'app\modules\goldoc\models\search',
            'crudSearchModelSuffix' => 'Search',
            'crudViewPath' => '@app/modules/goldoc/views',
            'crudPathPrefix' => '//goldoc/',
            'crudMessageCategory' => 'goldoc',
            'tablePrefix' => '',
        ],
        'db' => [
            'class' => '\dmstr\console\controllers\MysqlController',
            'noDataTables' => [
                'app_migration',
            ],
        ],
        'migrate' => [
            'class' => '\dmstr\console\controllers\MigrateController',
        ],
        'translate' => '\lajax\translatemanager\commands\TranslatemanagerController',
        'gearman' => [
            'class' => '\shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearman',
        ],
        'gearman-dear' => [
            'class' => '\shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearmanDear',
        ],
        'gearman-export' => [
            'class' => '\shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearmanExport',
        ],
        'gearman-hub-spot' => [
            'class' => '\shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearmanHubSpot',
        ],
    ],
    'modules' => [
        #so we can use command line tools of dektrium\user like change password/create password
        'user' => [
            'class' => 'dektrium\user\Module',
            #'layout' => 'SEE_DEPENDENCY_INJECTION',
            'defaultRoute' => 'admin',
            'adminPermission' => 'user-module',
            'enableRegistration' => false,
            'enableConfirmation' => false,
            'enableFlashMessages' => false,
            // did not work if yii cli is called without parameters
//            'controllerMap'       => [
//                'admin' => 'app\controllers\user\AdminController'
//            ],
            'modelMap' => [
                'User' => 'app\models\User',
                'Profile' => 'app\models\Profile',
            ]
        ],
    ]
];
