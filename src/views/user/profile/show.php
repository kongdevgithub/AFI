<?php
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \app\models\Profile $profile
 */


$this->title = $profile->user->getLabel();
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="box box-widget widget-user-2">
    <div class="widget-user-header bg-blue">
        <div class="widget-user-image">
            <?= $profile->user->getAvatar(65) ?>
        </div>
        <h3 class="widget-user-username"><?= $profile->user->getLabel() ?></h3>
        <h5 class="widget-user-desc"><?= $profile->user->email ?> | <?= $profile->phone ?: Yii::$app->settings->get('phone', 'app') ?></h5>
    </div>
    <div class="box-footer no-padding">
        <ul class="nav nav-stacked">
            <li style="padding: 20px;">
                <ul class="list-unstyled">
                    <?php if ($profile->user->getRoles()): ?>
                        <li>
                            <i class="fa fa-users text-muted"></i> <?= implode(' | <i class="fa fa-users text-muted"></i> ', $profile->user->getRoles()) ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($profile->location)): ?>
                        <li>
                            <i class="fa fa-map-marker text-muted"></i> <?= Html::encode($profile->location) ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($profile->website)): ?>
                        <li>
                            <i class="fa fa-globe text-muted"></i> <?= Html::a(Html::encode($profile->website), Html::encode($profile->website)) ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($profile->public_email)): ?>
                        <li>
                            <i class="fa fa-envelope text-muted"></i> <?= Html::a(Html::encode($profile->public_email), 'mailto:' . Html::encode($profile->public_email)) ?>
                        </li>
                    <?php endif; ?>
                    <li>
                        <i class="fa fa-clock-o text-muted"></i> <?= Yii::t('user', 'Joined on {0, date}', $profile->user->created_at) ?>
                    </li>
                </ul>
                <?php if (!empty($profile->bio)): ?>
                    <hr>
                    <p><?= Html::encode($profile->bio) ?></p>
                <?php endif; ?>
            </li>
            <!--
            <li><a href="#">Projects <span class="pull-right badge bg-blue">31</span></a></li>
            <li><a href="#">Tasks <span class="pull-right badge bg-aqua">5</span></a></li>
            -->
        </ul>
    </div>
</div>
