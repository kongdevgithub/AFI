<?php

use app\components\MenuItem;
use app\models\Company;
use app\models\User;
use yii\helpers\Html;

ini_set('memory_limit', '1G');
set_time_limit(60 * 10);

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Company Domain Check');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$companies = Company::find()->notDeleted()->all();
?>

<div class="row">
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Duplicate Domain'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $domains = [];
                $items = [];
                foreach ($companies as $company) {
                    if (!$company->website) {
                        continue;
                    }
                    if (isset($domains[$company->website])) {
                        $_company = $domains[$company->website];
                        $item = Html::a($_company->name, ['company/view', 'id' => $_company->id]);
                        $dupe = Html::a($company->name, ['company/view', 'id' => $company->id]);
                        $items[$company->staff_rep_id][$company->name . '.' . $company->id] = $dupe . ' <small>[' . $company->website . '][' . $item . ']</small>';
                    }
                    $domains[$company->website] = $company;
                }
                foreach ($items as $staff_rep_id => $_items) {
                    ksort($_items);
                    $staffRep = User::findOne($staff_rep_id);
                    echo Html::tag('h3', ($staffRep ? $staffRep->label : 'No Rep') . ' ' . count($_items));
                    echo Html::ul($_items, ['encode' => false]);
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'No Domain'); ?></h3>
            </div>
            <div class="box-body">
                <?php

                //$count = [
                //    'unmatched' => 0,
                //    'matched' => 0,
                //];

                //$websites = [];
                //$contacts = \app\components\Csv::csvToArray(Yii::$app->runtimePath . '/contacts.csv');
                //foreach ($contacts as $contact) {
                //    if (trim($contact['Website'])) {
                //        $websites[trim(strtolower($contact['Account Name']))] = trim($contact['Website']);
                //    }
                //}

                $items = [];
                foreach ($companies as $company) {
                    if (!$company->website) {
                        $link = Html::a($company->name, ['company/view', 'id' => $company->id]);
                        //$website = isset($websites[trim(strtolower($company->name))]) ? ' = ' . $websites[trim(strtolower($company->name))] : '';
                        $items[$company->staff_rep_id][$company->name . '.' . $company->id] = $link; // . $website;
                        //if ($website) {
                        //    $count['matched']++;
                        //} else {
                        //    $count['unmatched']++;
                        //}
                    }
                }

                //debug($count);

                foreach ($items as $staff_rep_id => $_items) {
                    ksort($_items);
                    $staffRep = User::findOne($staff_rep_id);
                    echo Html::tag('h3', ($staffRep ? $staffRep->label : 'No Rep') . ' ' . count($_items));
                    echo Html::ul($_items, ['encode' => false]);
                }
                ?>
            </div>
        </div>
    </div>
</div>






