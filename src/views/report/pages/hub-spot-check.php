<?php

use app\components\MenuItem;
use app\models\Company;
use app\models\Contact;
use yii\helpers\Html;
use yii\validators\EmailValidator;

ini_set('memory_limit', '1G');
set_time_limit(60 * 10);

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Hub Spot Check');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Hub Spot Check');
$this->params['nav'] = MenuItem::getReportsItems();

$companies = Company::find()->notDeleted()->all();
$contacts = Contact::find()->notDeleted()->all();
?>

<div class="row">
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Missing HubSpot ID'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($companies as $company) {
                    if (!$company->hubSpotCompany) {
                        $items[$company->name . '.' . $company->id] = Html::a($company->name, ['company/view', 'id' => $company->id]);
                    }
                }
                ksort($items);
                echo Html::ul($items, ['encode' => false]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Missing HubSpot ID'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($contacts as $contact) {
                    if (!$contact->hubSpotContact) {
                        $items[$contact->label . '.' . $contact->id] = Html::a($contact->label, ['contact/view', 'id' => $contact->id]);
                    }
                }
                ksort($items);
                echo Html::ul($items, ['encode' => false]);
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', '@afibranding.com.au Email'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($contacts as $contact) {
                    if (strpos($contact->email, '@afibranding.com.au') !== false) {
                        $items[$contact->label . '.' . $contact->id] = Html::a($contact->label, ['contact/view', 'id' => $contact->id]);
                    }
                }
                ksort($items);
                echo Html::ul($items, ['encode' => false]);
                ?>
            </div>
        </div>
    </div>
</div>






