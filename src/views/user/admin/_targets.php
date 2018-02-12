<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use app\models\Target;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\User $user
 * @var dektrium\user\models\Profile $profile
 */

?>

<?php $this->beginContent('@dektrium/user/views/admin/update.php', ['user' => $user]) ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>

<table class="table">
    <tr>
        <th></th>
        <?php foreach (range(1, 12) as $month) { ?>
            <th><?php echo date('F', strtotime('2000-' . $month . '-01')); ?></th>
        <?php } ?>
    </tr>
    <?php foreach (range(date('Y', strtotime('-4 years')), date('Y', strtotime('+6 years'))) as $year) { ?>
        <tr>
            <th><?php echo date('Y', strtotime($year . '-01-01')); ?></th>
            <?php foreach (range(1, 12) as $month) { ?>
                <td>
                    <?php
                    $target = Target::findOne([
                        'model_name' => $user->className(),
                        'model_id' => $user->id,
                        'date' => $year . '-' . $month . '-01',
                    ]);
                    $target = $target ? $target->target : '';
                    echo Html::textInput('Target[' . $year . '-' . $month . '-01]', $target, array('style' => 'width:75px;'));
                    ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
</table>


<div class="form-group">
    <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php $this->endContent() ?>
