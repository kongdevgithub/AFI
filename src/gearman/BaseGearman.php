<?php

namespace app\gearman;

use app\components\CommandStats;
use app\components\EmailManager;
use bedezign\yii2\audit\Audit;
use shakura\yii2\gearman\JobBase;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * BaseGearman
 */
abstract class BaseGearman extends JobBase
{

    /**
     * @var
     */
    private $_start;

    /**
     * @param \GearmanJob|null $job
     * @return mixed|void
     */
    public function execute(\GearmanJob $job = null)
    {
        $params = unserialize($job->workload())->params;
        $this->begin($params);
        echo substr(StringHelper::basename($this->className()), 0, -7) . ' ';
        $this->executeWorkload($params);
        echo ' ' . CommandStats::stats(null, null, true, microtime(true) - $this->_start) . "\n";
        $this->end();
    }

    /**
     * @param $params mixed
     */
    abstract function executeWorkload($params);

    /**
     * @param mixed $params
     */
    protected function begin($params)
    {
        // start the timer
        $this->_start = microtime(true);

        // set params
        $_GET = [];
        $_POST = [];
        $_GET['params'] = $params;

        // begin audit
        /** @var Audit $audit */
        $audit = Yii::$app->getModule('audit');
        $audit->getEntry(true, true);
        $audit->entry->route = 'gearman/' . Inflector::camel2id(static::classShortName());
        $audit->entry->save(false, ['route']);

        // open databases
        Yii::$app->db->open();
        Yii::$app->dbData->open();
        Yii::$app->dbAudit->open();
    }

    /**
     *
     */
    protected function end()
    {
        // end transactions
        while ($t = Yii::$app->db->getTransaction()) {
            $t->commit();
            EmailManager::sendDatabaseTransactionOpenAlert();
        }
        while ($t = Yii::$app->dbData->getTransaction()) {
            $t->commit();
            EmailManager::sendDatabaseTransactionOpenAlert();
        }
        while ($t = Yii::$app->dbAudit->getTransaction()) {
            $t->commit();
            EmailManager::sendDatabaseTransactionOpenAlert();
        }

        // end audit
        /** @var Audit $audit */
        $audit = Yii::$app->getModule('audit');
        $audit->onAfterRequest();
        $audit->entry->duration = microtime(true) - $this->_start;
        $audit->entry->save(false, ['duration']);

        // flush logger
        Yii::getLogger()->flush(true);

        // close databases
        Yii::$app->db->close();
        Yii::$app->dbData->close();
        Yii::$app->dbAudit->close();

        // kill application
        if (memory_get_usage() > $this->convertToBytes(ini_get('memory_limit')) / 2) {
            echo 'memory exhausted, exiting.';
            Yii::$app->end();
        }
    }

    /**
     * @param $from
     * @return mixed
     */
    private function convertToBytes($from)
    {
        $number = substr($from, 0, -1);
        switch (strtoupper(substr($from, -1))) {
            case 'K':
                return $number * 1024;
            case 'M':
                return $number * pow(1024, 2);
            case 'G':
                return $number * pow(1024, 3);
            default:
                return $from;
        }
    }

    /**
     * @return string
     */
    public static function classShortName()
    {
        return substr(StringHelper::basename(static::className()), 0, -7);
    }

}