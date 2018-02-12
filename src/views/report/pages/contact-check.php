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

$this->title = Yii::t('app', 'Contact Check');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Contact Check');
$this->params['nav'] = MenuItem::getReportsItems();

$contacts = Contact::find()->notDeleted()->all();
?>

<div class="row">
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Missing Email'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($contacts as $contact) {
                    $validator = new EmailValidator();
                    if (!$contact->email) {
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
                <h3 class="box-title"><?= Yii::t('app', 'Invalid Email'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($contacts as $contact) {
                    $validator = new EmailValidator();
                    if ($contact->email && !$validator->validate($contact->email, $error)) {
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
                <h3 class="box-title"><?= Yii::t('app', 'Duplicate Email'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($contacts as $contact) {
                    $duplicates = [];
                    foreach ($contacts as $_contact) {
                        if ($contact->id == $_contact->id || !$contact->email) {
                            continue;
                        }
                        if ($contact->email == $_contact->email) {
                            $duplicates[$_contact->label . '.' . $_contact->id] = Html::a($_contact->label, ['contact/view', 'id' => $_contact->id]);
                        }
                    }
                    if ($duplicates) {
                        ksort($duplicates);
                        $items[$contact->label] = Html::a($contact->label, ['contact/view', 'id' => $contact->id]) . Html::ul($duplicates, ['encode' => false]);
                    }
                }
                ksort($items);
                echo Html::ul($items, ['encode' => false]);
                ?>
            </div>
        </div>
    </div>
</div>






