<?php
use app\models\Note;
use app\components\ReturnUrl;
use yii\helpers\Html;

/* @var $this \yii\web\View */

if (Yii::$app->user->isGuest) {
    return;
}
$notes = Note::find()->andWhere(['model_name' => Yii::$app->className()])->notDeleted()->all();
if ($notes) {
    foreach ($notes as $note) {
        ?>
        <div class="alert <?= $note->important ? 'alert-danger' : 'alert-info' ?>" style="border-radius: 0;padding: 20px 30px; font-size: 16px;">
            <?php if ($note->model_name != Yii::$app->className() || Yii::$app->user->can('manager')) { ?>
                <div class="pull-right">
                    <?= Html::a('<i class="fa fa-pencil"></i>', ['/note/update', 'id' => $note->id, 'ru' => ReturnUrl::getToken()], [
                        'class' => 'btn btn-box-tool modal-remote',
                    ]) ?>
                    <?= Html::a('<i class="fa fa-trash"></i>', ['/note/delete', 'id' => $note->id, 'ru' => ReturnUrl::getToken()], [
                        'data-confirm' => Yii::t('app', 'Are you sure?'),
                        'data-method' => 'post',
                        'class' => 'btn btn-box-tool',
                    ]) ?>
                </div>
            <?php } ?>
            <h3 style="margin: 0 0 1em 0;"><?= $note->title ?></strong></h3>
            <?php
            echo nl2br($note->body);
            //echo Yii::$app->formatter->asNtext($note->body);
            ?>
        </div>
        <?php
    }
}