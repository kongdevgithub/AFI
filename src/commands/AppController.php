<?php

namespace app\commands;

use mikehaertl\shellcommand\Command;
use Yii;
use yii\console\Controller;

/**
 * Class AppController
 * @package app\commands
 */
class AppController extends Controller
{
    public $defaultAction = 'version';

    /**
     * Displays application version from ENV variable (read from version file).
     */
    public function actionVersion()
    {
        $this->stdout(Yii::$app->id . ' version ' . APP_VERSION);
        $this->stdout("\n");
    }

    /**
     * Setup admin user (create, update password, confirm).
     */
    public function actionSetupDb()
    {
        foreach (['', 'data', 'audit', 'goldoc'] as $db) {
            $this->stdout("Initializing database: $db\n");
            if ($db) {
                $this->run('db/create', [getenv('DATABASE_DSN_DB') . '_' . $db]);
            } else {
                $this->run('db/create');
            }
        }
        //$this->run('migrate/up', ['interactive' => (bool)getenv('APP_INTERACTIVE')]);
    }

    /**
     * Clear [application]/web/assets folder.
     */
    public function actionClearAssets()
    {
        $assets = Yii::getAlias('@web/assets');
        // Matches from 7-8 char folder names, the 8. char is optional
        $matchRegex = '"^[a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9]\?[a-z0-9]$"';
        // create $cmd command
        $cmd = 'cd "' . $assets . '" && ls | grep -e ' . $matchRegex . ' | xargs rm -rf ';
        // Set command
        $command = new Command($cmd);
        // Prompt user
        $delete = $this->confirm("\nDo you really want to delete web assets?", true);
        if ($delete) {
            // Try to execute $command
            if ($command->execute()) {
                echo "Web assets have been deleted.\n\n";
            } else {
                echo "\n" . $command->getError() . "\n";
                echo $command->getStdErr();
            }
        }
    }

}
