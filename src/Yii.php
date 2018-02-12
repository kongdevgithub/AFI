<?php

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code autocompletion.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include(__DIR__ . '/vendor/yiisoft/yii2/classes.php');
Yii::$container = new yii\di\Container;

/**
 * Class BaseApplication
 * Used for properties that are identical for both WebApplication and ConsoleApplication
 *
 * @property \yii\db\Connection $dbData
 * @property \yii\db\Connection $dbAudit
 * @property \yii\db\Connection $dbGoldoc
 * @property \yii\caching\FileCache $cacheFile
 * @property \pheme\settings\components\Settings $settings
 * @property \yii\mutex\FileMutex $mutex
 * @property \shakura\yii2\gearman\GearmanComponent $gearman
 * @property \shakura\yii2\gearman\GearmanComponent $gearmanDear
 * @property \shakura\yii2\gearman\GearmanComponent $gearmanExport
 * @property \shakura\yii2\gearman\GearmanComponent $gearmanHubSpot
 * @property \app\components\HubSpotApi $hubSpotApi
 * @property \app\components\DearApi $dearApi
 * @property \cornernote\workflow\manager\components\WorkflowDbSource $workflowSource
 * @property \app\components\User $user The user component. This property is read-only.
 * @property \yii\rbac\DbManager $authManager
 * @property \frostealth\yii2\aws\s3\Storage $s3
 * @property \app\components\Slack $slack
 * @property \app\components\AuthyApi $authyApi
 * @property \app\components\TwoFactor $twoFactor
 */
abstract class BaseApplication extends yii\base\Application
{
}

/**
 * Class WebApplication
 * Include only Web application related components here
 */
class WebApplication extends yii\web\Application
{
}

/**
 * Class ConsoleApplication
 * Include only Console application related components here
 */
class ConsoleApplication extends yii\console\Application
{
}