<?php
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Job $model
 */

echo DetailView::widget([
    'model' => false,
    'attributes' => [
        [
            'label' => Yii::t('app', 'Subject'),
            'value' => Yii::t('app', 'Your proforma invoice is ready!') . ' ' . $model->getTitle(),
        ],
        [
            'label' => Yii::t('app', 'From'),
            'value' => $model->staffRep->label . ' <' . $model->staffRep->email . '>',
        ],
        [
            'label' => Yii::t('app', 'To'),
            'value' => $model->contact->label . ' <' . $model->contact->email . '>',
        ],
        [
            'label' => Yii::t('app', 'BCC'),
            'value' => implode("\n", [
                $model->staffLead->id => $model->staffLead->label . ' <' . $model->staffLead->email . '>',
                $model->staffRep->id => $model->staffRep->label . ' <' . $model->staffRep->email . '>',
                $model->staffCsr->id => $model->staffCsr->label . ' <' . $model->staffCsr->email . '>',
            ]),
            'format' => 'ntext',
        ],
    ],
]);
