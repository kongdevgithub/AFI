<?php

namespace app\commands;

use yii\console\Controller;


class DevController extends Controller
{
    /**
     * Displays export database command
     *
     * @param string $dbType to provide database (if required) like audit or audit
     *
     * if you want export of audit or data
     *
     *
     * @param bool $structureOnly only database structure without any data by default its false
     *
     * @return int
     */
    public function actionDbExportCommand($dbType = '', $structureOnly = false)
    {
        $vPostFix = $this->getPostFixFromDbType($dbType);
        $vConnectionCommand = $this->getConnectionCommand($vPostFix);
        $aParam = [];
        if ($structureOnly) {
            $aParam[] = '--no-data';

        }
        $vParam = implode(" ", $aParam);
        $vFileToGzip = "gzip -c  >  backup{$vPostFix}.sql.gz ";
        $vDumpCommand = "mysqldump  $vParam $vConnectionCommand | $vFileToGzip ";
        echo $vDumpCommand . "\n";

        return self::EXIT_CODE_NORMAL;
    }

    protected function getPostFixFromDbType($dbType)
    {
        return $dbType ? '_' . $dbType : '';

    }

    /**
     * Displays import database command
     *
     * @param string $dbType to provide database (if required) like audit or audit
     *
     * if you want export of audit or data
     *
     * @return int
     */
    public function actionDbImportCommand($dbType = '')
    {
        $vPostFix = $this->getPostFixFromDbType($dbType);
        $vConnectionCommand = $this->getConnectionCommand($vPostFix);
        $vFileToGzip = "gunzip < backup{$vPostFix}.sql.gz ";
        $vDumpCommand = "$vFileToGzip | mysql $vConnectionCommand";
        echo $vDumpCommand . "\n";

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Returns OK if things are ok.
     * @return string
     */
    public function actionHealthCheck()
    {
        /**
         * TODO: add db, memcache check etc. and check parameters to check some/all issues
         *
         */
        echo "OK";
        return self::EXIT_CODE_NORMAL;
    }

    protected function getDBParam()
    {
        $vHost = getenv("DB_PORT_3306_TCP_ADDR");
        return [
          'user'     => getenv("DB_ENV_MYSQL_USER"),
          'host'     => $vHost,
          'password' => getenv("DB_ENV_MYSQL_PASSWORD"),
          'dbname'   => getenv("DATABASE_DSN_DB"),
        ];
    }

    protected function getConnectionCommand($dbPostFix)
    {
        $aInfoToSend = $this->getDBParam();
        $vCommand = "-h{$aInfoToSend['host']} -u{$aInfoToSend['user']} -p{$aInfoToSend['password']} {$aInfoToSend['dbname']}{$dbPostFix}";
        return $vCommand;
    }
    protected function getRootConnectionCommand()
    {
        $vRootPassword = getenv('DB_ENV_MYSQL_ROOT_PASSWORD');
        $aInfoToSend = $this->getDBParam();
        $vCommand = "-h{$aInfoToSend['host']} -uroot -p{$vRootPassword}";
        return $vCommand;
    }


    /**
     * console into server database
     *
     * @param bool $rootUser whether to use command as root user
     *  by default false
     */
    public function actionDbConsole($rootUser=false)
    {
        $vConnection = $rootUser ? $this->getRootConnectionCommand() :  $this->getConnectionCommand("");
        $vCommand = 'mysql ' . $vConnection;
        $descriptorSpec = array(
          0 => STDIN,
          1 => STDOUT,
          2 => STDERR,
        );
        $process = proc_open($vCommand, $descriptorSpec, $pipes);
        if (is_resource($process)) {
            proc_close($process);
        }
    }

    /**
     * Display DB connection command
     *
     * @param bool $rootUser whether to use command as root user
     *  by default false
     */
    public function actionDbInfo($rootUser=false)
    {
        $vConnection = $rootUser ? $this->getRootConnectionCommand() :  $this->getConnectionCommand("");
        $vCommand = 'mysql ' . $vConnection;
        echo $vCommand . "\n";
    }
}
