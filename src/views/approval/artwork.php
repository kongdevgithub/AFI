<?php
/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var string $key
 */
use app\components\ReturnUrl;
use kartik\date\DatePickerAsset;
use yii\helpers\Html;
use yii\helpers\Inflector;

DatePickerAsset::register($this);

$this->title = $model->getTitle();
if (Yii::$app->user->isGuest) {
    $this->params['heading'] = '';
} elseif (Yii::$app->user->can('staff')) {
    // staff
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['//job/index']];
    $this->params['breadcrumbs'][] = ['label' => 'job-' . $model->vid . ': ' . $model->name, 'url' => ['//job/view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t('app', 'Artwork Approval');
} else {
    // client
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['//client/job/index']];
    $this->params['breadcrumbs'][] = ['label' => 'job-' . $model->vid . ': ' . $model->name, 'url' => ['//client/job/view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t('app', 'Artwork Approval');
}

$approvalUrl = ['artwork-approval', 'id' => $model->id, 'key' => $key, 'ru' => ReturnUrl::getToken()];

$pdfUrl = ['artwork-pdf', 'id' => $model->id, 'key' => $key];

$filename = Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->id . '.pdf';
//$pdfUrl = ['artwork-pdf', 'filename' => $filename, 'id' => $model->id, 'key' => $key];

$allowApproval = true;
if ($model->status != 'job/production') {
    $allowApproval = false;
} else {
    $items = [];
    foreach ($model->products as $product) {
        foreach ($product->items as $item) {
            if ($item->quantity && explode('/', $item->status)[1] == 'approval') {
                $items[] = $item;
            }
        }
    }
    if (!count($items)) {
        $allowApproval = false;
    }
}

$approvalText = Yii::t('app', 'Accept All Artwork');
if ($allowApproval) {
    foreach ($model->products as $product) {
        foreach ($product->items as $item) {
            if ($item->quantity && explode('/', $item->status)[1] == 'change') {
                $approvalText = Yii::t('app', 'Accept Remaining Artwork');
            }
        }
    }
}
?>

<div class="approval-artwork">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Job') . ' #' . $model->vid . ' - ' . $model->name; ?></h3>
            <div class="box-tools pull-right">
                <?php if ($allowApproval) { ?>
                    <?= Html::a('<i class="fa fa-check"></i> ' . $approvalText, $approvalUrl, [
                        'class' => 'btn btn-success btn-sm modal-remote',
                    ]) ?>
                <?php } ?>
                <?= Html::a('<i class="fa fa-file-pdf-o"></i> ' . Yii::t('app', 'View PDF'), $pdfUrl, [
                    'class' => 'btn btn-default btn-sm',
                    'target' => '_blank',
                ]) ?>
            </div>
        </div>
        <div class="box-body">
            <?= $this->render('/job/_artwork-details', ['model' => $model, 'allowApproval' => $allowApproval, 'key' => $key]) ?>
        </div>
        <?php if ($allowApproval) { ?>
            <div class="box-footer text-right">
                <?= Html::a('<i class="fa fa-check"></i> ' . $approvalText, $approvalUrl, [
                    'class' => 'btn btn-success btn-xl modal-remote',
                ]) ?>
            </div>
        <?php } ?>
    </div>
</div>
