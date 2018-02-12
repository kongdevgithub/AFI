<?php
/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 * @var string $key
 */
use app\components\ReturnUrl;
use kartik\date\DatePickerAsset;
use yii\bootstrap\Alert;
use yii\helpers\Html;

DatePickerAsset::register($this);

$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf.css')));
$this->registerCss(file_get_contents(Yii::getAlias('@app/assets/web/css/pdf-' . $model->quote_template . '.css')));

$this->title = $model->getTitle();
if (Yii::$app->user->isGuest) {
    $this->params['heading'] = '';
} elseif (Yii::$app->user->can('staff')) {
    // staff
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['//job/index']];
    $this->params['breadcrumbs'][] = ['label' => 'job-' . $model->vid . ': ' . $model->name, 'url' => ['//job/view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t('app', 'Quote Approval');
} else {
    // client
    $this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Jobs'), 'url' => ['//client/job/index']];
    $this->params['breadcrumbs'][] = ['label' => 'job-' . $model->vid . ': ' . $model->name, 'url' => ['//client/job/view', 'id' => $model->id]];
    $this->params['breadcrumbs'][] = Yii::t('app', 'Quote Approval');
}

$approvalUrl = ['quote-approval', 'id' => $model->id, 'key' => $key, 'ru' => ReturnUrl::getToken()];
$artworkUrl = ['quote-artwork', 'id' => $model->id, 'key' => $key, 'ru' => ReturnUrl::getToken()];
if ($model->status != 'job/quote' || $model->quote_totals_format == 'hide-totals' || $model->hasForkQuantityProducts()) {
    $approvalUrl = false;
}

$pdfUrl = ['quote-pdf', 'id' => $model->id, 'key' => $key];
?>

<div class="approval-quote">
    <?php
    if (in_array($model->status, ['job/productionPending', 'job/production', 'job/despatch']) && $model->quote_approved_by) {
        echo Alert::widget([
            'body' => Yii::t('app', 'Quote has been accepted by {name}.', [
                'name' => $model->quote_approved_by,
            ]),
            'options' => ['class' => 'alert-info'],
            'closeButton' => false,
        ]);
    }
    ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Quote') . ' #' . $model->vid . ' - ' . $model->name; ?></h3>
            <div class="box-tools pull-right">
                <?php if ($approvalUrl) { ?>
                    <?= Html::a('<i class="fa fa-check"></i> ' . Yii::t('app', 'Accept Quote'), $approvalUrl, [
                        'class' => 'btn btn-success btn-sm modal-remote',
                    ]) ?>
                <?php } else { ?>
                    <?= Html::a('<i class="fa fa-upload"></i> ' . Yii::t('app', 'Upload Artwork'), $artworkUrl, [
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
            <?= $this->render('//job/_quote-details', ['model' => $model]) ?>
        </div>
        <div class="box-footer text-right">
            <?php if ($approvalUrl) { ?>
                <?= Html::a('<i class="fa fa-check"></i> ' . Yii::t('app', 'Accept Quote'), $approvalUrl, [
                    'class' => 'btn btn-success btn-xl modal-remote',
                ]) ?>
            <?php } else { ?>
                <?= Html::a('<i class="fa fa-upload"></i> ' . Yii::t('app', 'Upload Artwork'), $artworkUrl, [
                    'class' => 'btn btn-success btn-xl modal-remote',
                ]) ?>
            <?php } ?>
            <?= Html::a('<i class="fa fa-file-pdf-o"></i> ' . Yii::t('app', 'View PDF'), $pdfUrl, [
                'class' => 'btn btn-default btn-xl',
                'target' => '_blank',
            ]) ?>
        </div>
    </div>
</div>
