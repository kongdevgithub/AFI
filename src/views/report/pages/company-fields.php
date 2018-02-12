<?php

use app\components\Helper;
use app\components\MenuItem;
use app\models\Address;
use app\models\Company;
use app\models\Contact;
use app\models\User;
use bedezign\yii2\audit\models\AuditTrail;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

ini_set('memory_limit', '1G');
set_time_limit(60 * 10);

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Company Fields');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

/** @var AuditTrail[] $auditTrails */
$auditTrails = AuditTrail::find()
    ->andWhere(['in', 'model', [Company::className(), Contact::className(), Address::className()]])
    ->andWhere(['not in', 'field', ['updated_at', 'created_at', 'id', 'model_name', 'model_id']])
    ->andWhere(['between', 'created', '2017-03-20', '2017-03-21'])
    ->orderBy(['entry_id' => SORT_DESC, 'model_id' => SORT_ASC, 'field' => SORT_ASC, 'id' => SORT_DESC])
    ->all();

/** @var Address[] $addresses */
$addresses = [];

foreach ($auditTrails as $k => $v) {
    if ($v->entry->route != 'gearman/hub-spot-webhook') {
        unset($auditTrails[$k]);
        continue;
    }
    if ($v->model == Address::className()) {
        if (!isset($addresses[$v->model_id])) {
            $_address = Address::findOne($v->model_id);
            $addresses[$v->model_id] = $_address ? $_address->model_name : '';
        }
        if ($addresses[$v->model_id] != Company::className()) {
            unset($auditTrails[$k]);
            continue;
        }
    }
}

echo GridView::widget([
    'dataProvider' => new ArrayDataProvider([
        'allModels' => $auditTrails,
        'pagination' => ['pageSize' => 1000000],
        'sort' => false,
    ]),
    'layout' => '{items}',
    'columns' => [
        'created',
        [
            'label' => 'route',
            'value' => function ($model) {
                /** @var AuditTrail $model */
                return $model->entry->route;
            },
        ],
        [
            'attribute' => 'user_id',
            'value' => function ($model) {
                /** @var AuditTrail $model */
                $user = User::findOne($model->user_id);
                return $user ? $user->label : '';
            },
        ],
        [
            'attribute' => 'model_id',
            'group' => true,
            'value' => function ($model) {
                /** @var AuditTrail $model */
                if ($model->model == Company::className()) {
                    $company = Company::findOne($model->model_id);
                    return $company ? Html::a($company->name, ['//company/view', 'id' => $company->id]) : 'company-' . $model->model_id;
                }
                if ($model->model == Contact::className()) {
                    $contact = Contact::findOne($model->model_id);
                    return $contact ? Html::a($contact->label, ['//contact/view', 'id' => $contact->id]) : 'contact-' . $model->model_id;
                }
                if ($model->model == Address::className()) {
                    $address = Address::findOne($model->model_id);
                    $addressOutput = $address ? $address->type . '<br>' . $address->getLabel('<br>') : 'address-' . $model->model_id;
                    $company = Company::findOne($address->model_id);
                    $companyOutput = $company ? Html::a($company->name, ['//company/view', 'id' => $company->id]) : 'company-' . $address->model_id;
                    return $companyOutput . '<br>' . $addressOutput;
                }
                return '';
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'action',
            'group' => true,
            'subGroupOf' => 3,
            'format' => 'raw',
        ],
        [
            'attribute' => 'model',
            'format' => 'raw',
        ],
        [
            'attribute' => 'field',
            'format' => 'raw',
        ],
        [
            'label' => 'changes',
            'value' => function ($model) {
                /** @var AuditTrail $model */
                $old = Helper::getAuditTrailValue($model->model, $model->field, $model->old_value);
                $new = Helper::getAuditTrailValue($model->model, $model->field, $model->new_value);
                $diff = new \Diff(explode("\n", $old), explode("\n", $new));
                return $diff->render(new \Diff_Renderer_Html_Inline);
            },
            'format' => 'raw',
        ],
    ]
]);

echo 'end';