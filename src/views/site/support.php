<?php
/**
 * @var $this yii\web\View
 * @var $model \app\models\form\SupportForm
 */

use app\components\MenuItem;
use app\widgets\Menu;
use app\widgets\Nav;
use cornernote\shortcuts\Y;
use kartik\form\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\StringHelper;

$this->title = Yii::t('app', 'Support');
$this->params['heading'] = $this->title;
?>

<div class="row">
    <div class="col-md-8">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Report an Issue or Request Support'); ?></h3>
            </div>
            <div class="box-body">
                <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')) { ?>
                    <div class="alert alert-success">
                        Thank you for your support request. We will respond to you as soon as possible.
                    </div>
                <?php } else { ?>
                    <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>
                    <?= $form->field($model, 'subject') ?>
                    <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>
                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= Yii::t('app', 'Support Links'); ?></h3>
            </div>
            <div class="box-body">
                <?php
                $items = [];
                $items[] = [
                    'label' => Yii::t('app', 'Support Tickets'),
                    'url' => 'https://service.mrphp.com.au/projects/afi-console/issues',
                    'linkOptions' => ['target' => '_blank'],
                ];
                if (Y::user()->can('staff')) {
                    $items[] = [
                        'label' => Yii::t('app', 'Basecamp Project'),
                        'url' => 'https://basecamp.com/2521911/projects/13087103',
                        'linkOptions' => ['target' => '_blank'],
                    ];
                }
                echo Nav::widget([
                    'options' => ['class' => 'list-unstyled'],
                    'encodeLabels' => false,
                    'items' => $items,
                ]);
                ?>
            </div>
        </div>
    </div>
</div>


