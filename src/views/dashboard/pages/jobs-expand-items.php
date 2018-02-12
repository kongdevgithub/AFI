<?php

/**
 * @var yii\web\View $this
 */

use app\models\Job;

$this->title = Yii::t('app', 'Expand Items');

$item_type_id = isset($_GET['item_type_id']) ? $_GET['item_type_id'] : false;
$includeUnitStatus = isset($_GET['includeUnitStatus']) ? $_GET['includeUnitStatus'] : null;
$showColumns = isset($_GET['showColumns']) ? $_GET['showColumns'] : false;

if (!empty($_POST['expandRowKey'])) {
    $model = Job::findOne($_POST['expandRowKey']);
    echo $this->render('_jobs-expand-items', [
        'model' => $model,
        'item_type_id' => $item_type_id,
        'includeUnitStatus' => $includeUnitStatus,
        'showColumns' => $showColumns,
    ]);
}