<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use app\models\search\UserSearch;
use app\models\User;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', [
    'module' => Yii::$app->getModule('user'),
]) ?>

<?= $this->render('/admin/_menu') ?>

<?php Pjax::begin() ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'layout' => "{items}\n{pager}",
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}',
            'headerOptions' => ['style' => 'width:30px'],
        ],
        [
            'label' => false,
            'value' => function ($model) {
                /** @var User $model */
                return $model->getAvatar();
            },
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center', 'style' => 'width:40px;'],
        ],
        'username',
        [
            'attribute' => 'name',
            'value' => function ($model) {
                /** @var User $model */
                return $model->profile->name;
            },
            'format' => 'raw',
        ],
        'email',
        [
            'attribute' => 'phone',
            'value' => function ($model) {
                /** @var User $model */
                return $model->profile->phone;
            },
            'format' => 'raw',
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
        [
            'attribute' => 'two_factor',
            'label' => Yii::t('app', 'TFA'),
            'value' => function ($model) {
                /** @var User $model */
                $enabled = isset($model->two_factor['enabled']) && $model->two_factor['enabled'];
                if (!$enabled) {
                    return '';
                }
                return Html::a('<span class="glyphicon glyphicon-ok text-success"></span>', ['/user/admin/disable-two-factor', 'id' => $model->id], [
                    'data-confirm' => Yii::t('app', 'Are you sure?'),
                ]);
            },
            'format' => 'raw',
        ],
        [
            'header' => Yii::t('user', 'Block status'),
            'value' => function ($model) {
                if ($model->isBlocked) {
                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                    ]);
                }
            },
            'format' => 'raw',
        ],
        //[
        //    'attribute' => 'registration_ip',
        //    'value' => function ($model) {
        //        return $model->registration_ip == null
        //            ? '<span class="not-set">' . Yii::t('user', '(not set)') . '</span>'
        //            : $model->registration_ip;
        //    },
        //    'format' => 'html',
        //],
        //[
        //    'attribute' => 'created_at',
        //    'value' => function ($model) {
        //        if (extension_loaded('intl')) {
        //            return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
        //        } else {
        //            return date('Y-m-d G:i:s', $model->created_at);
        //        }
        //    },
        //],
        //[
        //    'header' => Yii::t('user', 'Confirmation'),
        //    'value' => function ($model) {
        //        if ($model->isConfirmed) {
        //            return '<div class="text-center">
        //                        <span class="text-success">' . Yii::t('user', 'Confirmed') . '</span>
        //                    </div>';
        //        } else {
        //            return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
        //                'class' => 'btn btn-xs btn-success btn-block',
        //                'data-method' => 'post',
        //                'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
        //            ]);
        //        }
        //    },
        //    'format' => 'raw',
        //    'visible' => Yii::$app->getModule('user')->enableConfirmation,
        //],
        //[
        //    'header' => Yii::t('user', 'Block status'),
        //    'value' => function ($model) {
        //        if ($model->isBlocked) {
        //            return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
        //                'class' => 'btn btn-xs btn-success btn-block',
        //                'data-method' => 'post',
        //                'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
        //            ]);
        //        } else {
        //            return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
        //                'class' => 'btn btn-xs btn-danger btn-block',
        //                'data-method' => 'post',
        //                'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
        //            ]);
        //        }
        //    },
        //    'format' => 'raw',
        //],
    ],
]); ?>

<?php Pjax::end() ?>
