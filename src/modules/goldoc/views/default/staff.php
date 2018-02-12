<?php

use app\components\BulkQuoteHelper;
use app\components\quotes\components\BaseComponentQuote;
use app\components\quotes\items\BaseItemQuote;
use app\components\quotes\jobs\BaseJobQuote;
use app\components\quotes\products\BaseProductQuote;
use app\models\ProductType;
use app\models\ProductTypeToItemType;
use app\models\search\UserSearch;
use app\models\User;
use app\components\ReturnUrl;
use kartik\grid\GridView;
use yii\bootstrap\Nav;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('goldoc', 'Staff');

//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $this->title; ?></h3>
    </div>
    <div class="box-body">

        <?php

        /** @var UserSearch $searchModel */
        $searchModel = Yii::createObject(UserSearch::className());
        $dataProvider = $searchModel->search([]);
        $dataProvider->query->andWhere(['id' => ArrayHelper::merge(
            Yii::$app->authManager->getUserIdsByRole('goldoc-manager'),
            Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc'),
            Yii::$app->authManager->getUserIdsByRole('goldoc-active'),
            Yii::$app->authManager->getUserIdsByRole('goldoc-goldoc-manager'),
            Yii::$app->authManager->getUserIdsByRole('goldoc-active-manager')
        )]);
        $dataProvider->query->andWhere(['blocked_at' => null]);
        $dataProvider->sort->defaultOrder = ['profile.name' => SORT_ASC];
        $dataProvider->sort->attributes['profile.name'] = [
            'asc' => ['profile.name' => SORT_ASC],
            'desc' => ['profile.name' => SORT_DESC],
            'default' => SORT_ASC,
        ];

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => false,
            'filterPosition' => false,
            'layout' => "{items}\n{pager}",
            'columns' => [
                [
                    'label' => false,
                    'value' => function ($model) {
                        /** @var User $model */
                        return Html::a($model->getAvatar(), ['/user/profile/show', 'id' => $model->id], ['class' => 'modal-remote']);
                    },
                    'format' => 'raw',
                    'contentOptions' => ['class' => 'text-center', 'style' => 'width:40px;'],
                ],
                [
                    'attribute' => 'username',
                    'enableSorting' => false,
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'name',
                    'value' => function ($model) {
                        /** @var User $model */
                        return $model->profile->name;
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'email',
                    'enableSorting' => false,
                    'format' => 'email',
                ],
                [
                    'attribute' => 'phone',
                    'value' => function ($model) {
                        /** @var User $model */
                        return $model->profile->phone ? Html::a($model->profile->phone, 'tel:' . preg_replace('/[^0-9]/', '', $model->profile->phone)) : '';
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'bio',
                    'value' => function ($model) {
                        /** @var User $model */
                        return $model->profile->bio ? $model->profile->bio : '';
                    },
                    'enableSorting' => false,
                ],
                [
                    'attribute' => 'role',
                    'value' => function ($model) {
                        /** @var User $model */
                        return implode(', ', $model->getRoles());
                    },
                    'filter' => ArrayHelper::map(Yii::$app->authManager->getRoles(), 'name', 'name'),
                    'format' => 'raw',
                ],
            ],
        ]);
        ?>
    </div>
</div>
