<?php

use app\components\MenuItem;
use app\models\Company;
use yii\helpers\Html;

ini_set('memory_limit', '1G');
set_time_limit(60 * 10);

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('app', 'Company Check');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Reports'), 'url' => ['report/index']];
//$this->params['breadcrumbs'][] = $this->title;
$this->params['nav'] = MenuItem::getReportsItems();

$companies = Company::find()->notDeleted()->all();
?>

<div class="row">
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'No Contacts'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($companies as $company) {
                    if (!$company->contacts) {
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
                <h3 class="box-title"><?= Yii::t('app', 'Exact Duplicates'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                foreach ($companies as $company) {
                    $duplicates = Company::find()->notDeleted()->andWhere([
                        'name' => $company->name,
                    ])->andWhere('id <> :id', [':id' => $company->id])->all();
                    if ($duplicates) {
                        $dupes = [];
                        foreach ($duplicates as $duplicate) {
                            $dupes[$duplicate->name . '.' . $duplicate->id] = Html::a($duplicate->name, ['company/view', 'id' => $duplicate->id]);
                        }
                        ksort($dupes);
                        $items[$company->name] = Html::a($company->name, ['company/view', 'id' => $company->id]) . Html::ul($dupes, ['encode' => false]);
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
                <h3 class="box-title"><?= Yii::t('app', 'Possible Duplicates'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                $done = [];
                foreach ($companies as $company) {
                    if (isset($done[$company->id])) {
                        continue;
                    }

                    $duplicates = [];
                    foreach ($companies as $_company) {
                        if ($company->id == $_company->id) {
                            continue;
                        }
                        if (soundex($company->name) == soundex($_company->name)) {
                            if (levenshtein($company->name, $_company->name) < 10) {
                                $duplicates[] = $_company;
                                $done[$_company->id] = $_company->id;
                            }
                        }
                    }
                    if ($duplicates) {
                        $dupes = [];
                        foreach ($duplicates as $duplicate) {
                            $dupes[$duplicate->name . '.' . $duplicate->id] = Html::a($duplicate->name, ['company/view', 'id' => $duplicate->id]);
                        }
                        ksort($dupes);
                        $items[$company->name . '.' . $company->id] = Html::a($company->name, ['company/view', 'id' => $company->id]) . Html::ul($dupes, ['encode' => false]);
                    }
                }
                ksort($items);
                echo Html::ul($items, ['encode' => false]);
                ?>
            </div>
        </div>
    </div>
</div>






