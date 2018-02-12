<?php

use app\components\BulkQuoteHelper;
use app\components\MenuItem;
use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\jobs\BaseJobQuote;
use app\components\quotes\products\BaseProductQuote;
use app\models\ProductType;
use app\models\ProductTypeToItemType;
use app\models\User;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\bootstrap\Nav;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Permissions');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$authManager = Yii::$app->authManager;

foreach ($authManager->getRoles() as $role) {
    ?>
    <div class="box collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $role->description . ' [' . $role->name . ']'; ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="box-body" style="display: none;">
            <div class="row">
                <div class="col-md-6">
                    <?php
                    $users = [];
                    foreach ($authManager->getUserIdsByRole($role->name) as $user_id) {
                        $user = User::findOne($user_id);
                        if ($user) {
                            $users[] = $user->label;
                        }
                    }
                    asort($users);
                    echo Html::ul($users);
                    ?>
                </div>
                <div class="col-md-6">
                    <?php
                    $permissions = [];
                    foreach ($authManager->getPermissionsByRole($role->name) as $permission) {
                        $permissions[] = $permission->name;
                    }
                    asort($permissions);
                    echo Html::ul($permissions);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}


