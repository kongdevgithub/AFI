<?php

use yii\web\JsExpression;
use yii\web\UrlNormalizer;

date_default_timezone_set(getenv('APP_TIMEZONE'));
ini_set('memory_limit', '1024M');
ini_set('max_input_vars', 10000);

function debug($var, $name = null, $attributesOnly = true)
{
    $bt = debug_backtrace();
    $file = str_ireplace(dirname(dirname(__FILE__)), '', $bt[0]['file']);
    if (!class_exists('\yii\db\BaseActiveRecord', false))
        $attributesOnly = false;
    $name = $name ? '<b><span style="font-size:18px;">' . $name . ($attributesOnly ? ' [attributes]' : '') . '</span></b>:<br/>' : '';
    echo '<div style="background: #FFFBD6">';
    echo '<span style="font-size:12px;">' . $name . ' ' . $file . ' on line ' . $bt[0]['line'] . '</span>';
    echo '<div style="border:1px solid #000;">';
    echo '<pre>';
    if (is_scalar($var)) {
        var_dump($var);
    } elseif ($attributesOnly && $var instanceof \yii\db\BaseActiveRecord) {
        print_r($var->attributes);
    } elseif ($attributesOnly && is_array($var) && current($var) instanceof \yii\db\BaseActiveRecord) {
        foreach ($var as $k => $_var) {
            $var[$k] = $_var->attributes;
        }
        print_r($var);
    } else {
        print_r($var);
    }
    echo '</pre></div></div>';
}


Yii::$container->set('cornernote\gii\giiant\crud\providers\DateProvider', [
    'columnNames' => ['due_date'],
]);
Yii::$container->set('yii\widgets\LinkPager', [
    'firstPageLabel' => '<span class="fa fa-fast-backward"></span>',
    'lastPageLabel' => '<span class="fa fa-fast-forward"></span>',
    'prevPageLabel' => '<span class="fa fa-step-backward"></span>',
    'nextPageLabel' => '<span class="fa fa-step-forward"></span>',
]);
Yii::$container->set('yii\bootstrap\Alert', [
    'closeButton' => ['label' => '<i class="fa fa-times"></i>'],
]);
Yii::$container->set('yii\bootstrap\ActiveForm', [
    'errorSummaryCssClass' => 'alert alert-danger error-summary',
    'options' => [
        'autocomplete' => 'off',
    ],
]);
Yii::$container->set('yii\bootstrap\ActiveField', [
    'horizontalCssClasses' => [
        'wrapper' => 'col-sm-9',
        'hint' => 'col-sm-12',
    ],
]);
Yii::$container->set('kartik\form\ActiveForm', [
    'options' => [
        'autocomplete' => 'off',
    ],
//    'formConfig' => [
//        'labelSpan' => 4,
//    ],
    'errorSummaryCssClass' => 'alert alert-danger error-summary',
    'enableClientValidation' => false,
]);
Yii::$container->set('kartik\form\ActiveField', [
    'hintType' => 1,
]);
Yii::$container->set('dektrium\user\controllers\SecurityController', [
    'layout' => '@app/views/layouts/narrow',
]);
Yii::$container->set('dektrium\user\controllers\RecoveryController', [
    'layout' => '@app/views/layouts/narrow',
]);
Yii::$container->set('kartik\grid\ExpandRowColumn', [
    'detailAnimationDuration' => 200,
]);
Yii::$container->set('kartik\grid\CheckboxColumn', [
    'vAlign' => 'top',
]);
Yii::$container->set('yii\data\Pagination', [
    'pageSizeLimit' => false,
]);
Yii::$container->set('kartik\select2\Select2', [
    'options' => [
        'multiple' => false,
        'theme' => 'krajee',
        'placeholder' => '',
        'language' => 'en-US',
        'width' => '100%',
    ],
    'pluginOptions' => [
        'allowClear' => true,
    ],
]);


// Basic configuration, used in web and console applications
return [
    'id' => getenv('APP_ID'),
    'name' => getenv('APP_NAME'),
    'timeZone' => getenv('APP_TIMEZONE'),
    'language' => 'en',
    'basePath' => dirname(__DIR__),
    'vendorPath' => '@app/../vendor',
    'runtimePath' => '@app/../runtime',
    // Bootstrapped modules are loaded in every request
    'bootstrap' => [
        'log',
    ],
    'aliases' => [
        'backend' => '@vendor/dmstr/yii2-backend-module/src',
        'root' => '@app/..',
        'data' => '@app/data',
        'docs' => '@app/../docs',
        'common' => '@app',
        'print-spool' => '@webroot/print-spool',
        'client' => '@app/modules/client',
        'goldoc' => '@app/modules/goldoc',
    ],
    'params' => [
        'adminEmail' => getenv('APP_ADMIN_EMAIL'),
        's3BucketUrl' => 'https://' . getenv('S3_BUCKET'),
        'context.menuItems' => [],
        'yii.migrations' => [
            //getenv('APP_MIGRATION_LOOKUP'),
            '@yii/rbac/migrations',
            '@dektrium/user/migrations',
            '@bedezign/yii2/audit/migrations',
            '@vendor/lajax/yii2-translate-manager/migrations',
            '@vendor/pheme/yii2-settings/migrations',
            '@vendor/dmstr/yii2-prototype-module/src/migrations',
            '@vendor/cornernote/yii2-workflow-manager/src/migrations',
            '@vendor/mar/yii2-simple-eav/migrations',
        ],
    ],
    'components' => [
        'assetManager' => [
            'appendTimestamp' => YII_ENV_PROD ? true : false,
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => [
                    'depends' => [
                        'app\assets\JuiFixAsset',
                    ],
                ],
            ],
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            //'defaultRoles' => ['guest'],
        ],
        'authyApi' => [
            'class' => 'app\components\AuthyApi',
            'apiKey' => getenv('AUTHY_API_KEY'),
            'defaultCountryCode' => 61,
        ],
        'cache' => [
            //'class' => 'yii\caching\ApcCache',
            //'useApcu' => true, // required for PHP 7
            'class' => 'yii\caching\MemCache',
            'keyPrefix' => getenv('APP_ID'),
            'servers' => [
                [
                    'host' => getenv('MEMCACHED_HOST'),
                    'port' => getenv('MEMCACHED_PORT'),
                    'weight' => 60,
                ],
            ],
        ],
        'cacheFile' => [
            'class' => 'yii\caching\FileCache',
        ],
        'cacheDb' => [
            'class' => 'yii\caching\DbCache',
        ],
        //'cacheModel' => [
        //    'class' => 'yii\caching\MemCache',
        //    'keyPrefix' => getenv('APP_ID') . '.model',
        //],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DATABASE_DSN'),
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
            //'enableQueryCache' => true,
            //'queryCache' => 'cacheModel',
        ],
        'dbData' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DATABASE_DSN') . '_data',
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
            //'enableQueryCache' => true,
            //'queryCache' => 'cacheModel',
        ],
        'dbAudit' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DATABASE_DSN') . '_audit',
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
            //'enableQueryCache' => true,
            //'queryCache' => 'cacheModel',
        ],
        'dbV3' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . getenv('DB_PORT_3306_TCP_ADDR') . ';port=' . getenv('DB_PORT_3306_TCP_PORT') . ';dbname=console_afi3',
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
            //'enableQueryCache' => true,
            //'queryCache' => 'cacheModel',
        ],
        'dbGoldoc' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DATABASE_DSN') . '_goldoc',
            'username' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'charset' => 'utf8',
            'tablePrefix' => getenv('DATABASE_TABLE_PREFIX'),
            'enableSchemaCache' => YII_ENV_PROD ? true : false,
            //'enableQueryCache' => true,
            //'queryCache' => 'cacheModel',
        ],
        'dearApi' => [
            'class' => 'app\components\DearApi',
            'accountId' => '136e1142-0571-4f17-b835-0ebe849b19c4',
            'applicationKey' => '50de9ef8-d5ab-7124-a668-1dca96a54c8c',
        ],
        'formatter' => [
            'timeZone' => getenv('APP_TIMEZONE'),
            'dateFormat' => 'php:d-m-Y',
            'datetimeFormat' => 'php:d-m-Y g:i A',
            'timeFormat' => 'php:g:i A',
            'nullDisplay' => '',
        ],
        'gearman' => [
            'class' => 'shakura\yii2\gearman\GearmanComponent',
            'servers' => [
                ['host' => getenv('GEARMAN_HOST') ?: '127.0.0.1', 'port' => getenv('GEARMAN_PORT') ?: '4730'],
            ],
            'user' => 'www-data',
            'jobs' => [
                getenv('APP_ID') . '-job-quote' => [
                    'class' => 'app\gearman\JobQuoteGearman',
                ],
                getenv('APP_ID') . '-job-product-import' => [
                    'class' => 'app\gearman\JobProductImportGearman',
                ],
                getenv('APP_ID') . '-job-build' => [
                    'class' => 'app\gearman\JobBuildGearman',
                ],
                getenv('APP_ID') . '-log-slack' => [
                    'class' => 'app\gearman\LogSlackGearman',
                ],
                getenv('APP_ID') . '-packageWorkflow-afterEnter-collected' => [
                    'class' => 'app\gearman\PackageWorkflowAfterEnterCollectedGearman',
                ],
                getenv('APP_ID') . '-packageWorkflow-afterLeave-collected' => [
                    'class' => 'app\gearman\PackageWorkflowAfterLeaveCollectedGearman',
                ],
                getenv('APP_ID') . '-pickupWorkflow-afterEnter-collected' => [
                    'class' => 'app\gearman\PickupWorkflowAfterEnterCollectedGearman',
                ],
                getenv('APP_ID') . '-pickupWorkflow-afterLeave-collected' => [
                    'class' => 'app\gearman\PickupWorkflowAfterLeaveCollectedGearman',
                ],
                getenv('APP_ID') . '-goldocProductWorkflow-afterEnter-production' => [
                    'class' => 'app\gearman\GoldocProductWorkflowAfterEnterProductionGearman',
                ],
            ],
        ],
        'gearmanDear' => [
            'class' => 'shakura\yii2\gearman\GearmanComponent',
            'servers' => [
                ['host' => getenv('GEARMAN_HOST') ?: '127.0.0.1', 'port' => getenv('GEARMAN_PORT') ?: '4730'],
            ],
            'user' => 'www-data',
            'jobs' => [
                getenv('APP_ID') . '-dear-push' => [
                    'class' => 'app\gearman\DearPushGearman',
                ],
            ],
        ],
        'gearmanExport' => [
            'class' => 'shakura\yii2\gearman\GearmanComponent',
            'servers' => [
                ['host' => getenv('GEARMAN_HOST') ?: '127.0.0.1', 'port' => getenv('GEARMAN_PORT') ?: '4730'],
            ],
            'user' => 'www-data',
            'jobs' => [
                getenv('APP_ID') . '-export' => [
                    'class' => 'app\gearman\ExportGearman',
                ],
            ],
        ],
        'gearmanHubSpot' => [
            'class' => 'shakura\yii2\gearman\GearmanComponent',
            'servers' => [
                ['host' => getenv('GEARMAN_HOST') ?: '127.0.0.1', 'port' => getenv('GEARMAN_PORT') ?: '4730'],
            ],
            'user' => 'www-data',
            'jobs' => [
                getenv('APP_ID') . '-hub-spot-webhook' => [
                    'class' => 'app\gearman\HubSpotWebhookGearman',
                ],
                getenv('APP_ID') . '-hub-spot-push' => [
                    'class' => 'app\gearman\HubSpotPushGearman',
                ],
            ],
        ],
        'httpclient' => [
            'class' => 'yii\httpclient\Client',
        ],
        'hubSpotApi' => [
            'class' => 'app\components\HubSpotApi',
            'apiKey' => getenv('HUBSPOT_API_KEY'),
            'clientId' => getenv('HUBSPOT_CLIENT_ID'),
            'clientSecret' => getenv('HUBSPOT_CLIENT_SECRET'),
            'redirect' => ['/hub-spot/oauth'],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'db' => 'db',
                    'sourceLanguage' => 'en',
                    'sourceMessageTable' => '{{%language_source}}',
                    'messageTable' => '{{%language_translate}}',
                    'cachingDuration' => 86400,
                    'enableCaching' => YII_ENV_PROD ? true : false,
                ],
            ],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'viewPath'         => '@common/mail',
            'useFileTransport' => YII_ENV_PROD ? false : true,
            //'transport' => [
            //    'class' => 'Swift_SmtpTransport',
            //    'host' => 'smtp.custom.local',
            //],
            //'transport' => [
            //    'class' => 'Swift_SmtpTransport',
            //    'host' => 'smtp.gmail.com',
            //    'username' => 'username@gmail.com',
            //    'password' => 'password',
            //    'port' => '587',
            //    'encryption' => 'tls',
            //],
        ],
        'mutex' => [
            'class' => 'yii\mutex\FileMutex',
        ],
        //'s3' => [ // todo php 5.6+
        //    'class' => 'frostealth\yii2\aws\s3\Service',
        //    'credentials' => [
        //        'key' => getenv('S3_KEY'),
        //        'secret' => getenv('S3_SECRET'),
        //    ],
        //    'region' => 's3-ap-southeast-2',
        //    'defaultBucket' => getenv('S3_BUCKET'),
        //    'defaultAcl' => 'public-read',
        //],
        's3' => [
            'class' => 'frostealth\yii2\aws\s3\Storage',
            'credentials' => [
                'key' => getenv('S3_KEY'),
                'secret' => getenv('S3_SECRET'),
            ],
            'region' => 'ap-southeast-2',
            'bucket' => getenv('S3_BUCKET'),
            'cdnHostname' => 'https://' . getenv('S3_BUCKET'),
            'defaultAcl' => 'public-read',
            //'debug' => true,
        ],
        'settings' => [
            'class' => 'pheme\settings\components\Settings',
        ],
        'slack' => [
            'class' => 'app\components\Slack',
            'url' => getenv('SLACK_URL'),
            'username' => getenv('SLACK_USERNAME'),
            'icon' => getenv('SLACK_ICON'),
        ],
        'twoFactor' => [
            'class' => 'app\components\TwoFactor',
            'active' => getenv('TWO_FACTOR_ACTIVE'),
            'domain' => getenv('TWO_FACTOR_DOMAIN'),
            'issuer' => getenv('TWO_FACTOR_ISSUER'),
        ],
        'user' => [
            'class' => 'app\components\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/user/security/login'],
            'identityClass' => 'app\models\User',
            'rootUsers' => ['admin'],
            'enableRootWarningFlash' => false,
        ],
        'urlManager' => [
            'class' => 'codemix\localeurls\UrlManager',
            'enablePrettyUrl' => getenv('APP_PRETTY_URLS') ? true : false,
            'showScriptName' => getenv('YII_ENV_TEST') ? true : false,
            //'enableDefaultLanguageUrlCode' => true,
            //'baseUrl' => getenv('APP_BASE_URL') ?: '/',
            'rules' => [
                'docs' => 'docs/index',
                'docs/index' => 'docs/index',
                'docs/<file:[\w-\./]+>' => 'docs/view',
                'help/<help:[\w-\./]+>' => 'help/index',
                'report/<report:[\w-\./]+>' => 'report/index',
                'dashboard/print' => 'dashboard/print',
                'dashboard/<dashboard:[\w-\./]+>' => 'dashboard/index',
                'print-spool/view/<spool:[\w-\./]+>' => 'print-spool/view',
                'print-spool/count/<spool:[\w-\./]+>' => 'print-spool/count',
                'print-spool/download/<spool:[\w-\./]+>/<file:[\w-\./]+>' => 'print-spool/download',
                'print-spool/delete/<spool:[\w-\./]+>/<file:[\w-\./]+>' => 'print-spool/delete',
                'goldoc/report/<report:[\w-\./]+>' => 'goldoc/report/index',
                'goldoc/dashboard/print' => 'goldoc/dashboard/print',
                'goldoc/dashboard/<dashboard:[\w-\./]+>' => 'goldoc/dashboard/index',
            ],
            'normalizer' => [
                'class' => 'yii\web\UrlNormalizer',
                // use temporary redirection instead of permanent for debugging
                'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
            ],
            'languages' => explode(',', getenv('APP_LANGUAGES')),
        ],
        'view' => [
            'class' => 'yii\web\View',
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@app/views/user'
                ],
            ],
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => ['html' => '\yii\helpers\Html'],
                    'uses' => ['yii\bootstrap'],
                ],
            ],
        ],
        'workflowSource' => [
            //'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
            'class' => 'cornernote\workflow\manager\components\WorkflowDbSource',
            'definitionCache' => YII_ENV_PROD ? 'cache' : null,
            //'definitionCache' => 'cache',
            //'definitionLoader' => [
            //    'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
            //'namespace' => '@app/models/workflows'
            //],
            //'parser' => 'raoul2000\workflow\source\file\MinimalArrayParser',
            //'classMap' => [
            //    self::TYPE_WORKFLOW => 'my\custom\implementation\Workflow',
            //    self::TYPE_STATUS => 'my\custom\implementation\Status',
            //    self::TYPE_TRANSITION => 'my\custom\implementation\Transition'
            //]
        ],
    ],
    'modules' => [
        'audit' => [
            'class' => 'bedezign\yii2\audit\Audit',
            'db' => 'dbAudit',
            'ignoreActions' => ['audit/*', 'debug/*', 'audit-alert/index'],
            'accessRoles' => ['admin'],
            'userIdentifierCallback' => ['app\models\User', 'userIdentifierCallback'],
            'panels' => [
                'audit/error' => [
                    'maxAge' => 30,
                ],
                'audit/javascript' => [
                    'maxAge' => 30,
                ],
                'audit/request' => [
                    'ignoreKeys' => ['SERVER'],
                    'maxAge' => 365,
                ],
                'audit/trail' => [
                    'maxAge' => 365,
                ],
                'audit/mail' => [
                    'maxAge' => 365,
                ],
                //'audit/log' => [
                //    'maxAge' => 30,
                //],
                //'audit/db' => [
                //    'maxAge' => 30,
                //],
                //'audit/profiling' => [
                //    'maxAge' => 30,
                //],
            ],
        ],
        'backend' => [
            'class' => 'dmstr\modules\backend\Module',
            'layout' => '@app/views/layouts/main',
        ],
        'client' => [
            'class' => 'app\modules\client\Module',
        ],
        //'crud' => [
        //    'class' => 'app\modules\crud\Module',
        //    'layout' => '@app/views/layouts/main',
        //],
        'goldoc' => [
            'class' => 'app\modules\goldoc\Module',
        ],
        'prototype' => [
            'class' => 'dmstr\modules\prototype\Module',
            'layout' => '@app/views/layouts/box',
        ],
        'rbac' => [
            'class' => 'dektrium\rbac\Module',
            'layout' => '@app/views/layouts/box',
            'enableFlashMessages' => false,
            'adminPermission' => 'rbac-module',
        ],
        'supervisor' => [
            'class' => 'supervisormanager\Module',
            'authData' => [
                'user' => 'supervisor_user',
                'password' => 'supervisor_pass',
                'url' => 'http://127.0.0.1:9001/RPC2', // Set by default
            ],
        ],
        'translatemanager' => [
            'class' => 'lajax\translatemanager\Module',
            'root' => '@app/views',
            'layout' => '@app/views/layouts/main',
            'allowedIPs' => ['*'],
            'roles' => ['translate-module'],
        ],
        'workflow' => [
            'class' => 'cornernote\workflow\manager\Module',
            'layout' => '@app/views/layouts/box',
        ],
    ]
];
