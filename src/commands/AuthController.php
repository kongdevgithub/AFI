<?php

namespace app\commands;

use app\models\ProductType;
use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

/**
 * Class AuthController
 * @package app\commands
 */
class AuthController extends Controller
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $auth = Yii::$app->authManager;

        // APP - add controller actions
        foreach ($this->getAllControllerActions('@app') as $action) {
            $name = str_replace('/', '_', 'app/' . $action);
            $permission = $auth->getPermission($name);
            if (!$permission) {
                $permission = $auth->createPermission($name);
                $permission->description = 'app/' . $action;
                $auth->add($permission);
                $this->stdout('added: ' . $name . "\n");
            }
        }

        // APP - add product types
        $permission = $auth->getPermission('_product-type');
        if (!$permission) {
            $permission = $auth->createPermission('_product-type');
            $permission->description = 'product type: ALL';
            $auth->add($permission);
            $this->stdout('added: ' . $permission->name . "\n");
        }
        foreach (ProductType::find()->all() as $productType) {
            $name = '_product-type_' . $productType->id;
            $description = 'product type: ' . $productType->getBreadcrumbString(' > ');
            foreach (['', '_read'] as $type) {
                $_name = $name . $type;
                $_description = $description . ($type ? ' - ' . substr($type, 1) : '');
                $permission = $auth->getPermission($_name);
                if (!$permission) {
                    $permission = $auth->createPermission($_name);
                    $permission->description = $_description;
                    $auth->add($permission);
                    $this->stdout('added: ' . $permission->name . "\n");
                } elseif ($permission->description != $_description) {
                    $permission->description = $_description;
                    $auth->update($_name, $permission);
                    $this->stdout('updated: ' . $permission->name . "\n");
                }
            }
        }

        // GOLDOC - add controller actions
        foreach ($this->getAllControllerActions('@goldoc') as $action) {
            $name = str_replace('/', '_', 'goldoc/' . $action);
            $permission = $auth->getPermission($name);
            if (!$permission) {
                $permission = $auth->createPermission($name);
                $permission->description = 'goldoc/' . $action;
                $auth->add($permission);
                $this->stdout('added: ' . $name . "\n");
            }
        }


        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    protected function getAllControllerActions($path, $recursive = true)
    {
        $controllers = FileHelper::findFiles(Yii::getAlias($path . '/controllers'), ['recursive' => $recursive]);
        $actions = [];
        foreach ($controllers as $controller) {
            $contents = file_get_contents($controller);
            $controllerId = Inflector::camel2id(substr(basename($controller), 0, -14));
            $actions[$controllerId] = $controllerId;
            preg_match_all('/public function action(\w+?)\(/', $contents, $result);
            foreach ($result[1] as $action) {
                $actionId = Inflector::camel2id($action);
                if ($actionId == 's') continue; // actions()
                $route = $controllerId . '/' . $actionId;
                $actions[$route] = $route;
            }
            $pagesFolder = Yii::getAlias($path . '/views/' . $controllerId . '/pages');
            if (is_dir($pagesFolder)) {
                $pages = FileHelper::findFiles($pagesFolder, ['recursive' => false]);
                if ($pages) {
                    foreach ($pages as $page) {
                        $actionId = substr(basename($page), 0, -4);
                        if (substr($actionId, 0, 1) == '_') {
                            continue;
                        }
                        $route = $controllerId . '/' . $actionId;
                        $actions[$route] = $route;
                    }
                }
            }
        }
        asort($actions);
        return $actions;
    }

}
