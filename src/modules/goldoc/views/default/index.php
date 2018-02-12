<?php
/* @var $this yii\web\View */

use app\modules\goldoc\components\MenuItem;
use app\widgets\Nav;
use yii\bootstrap\Html;

$this->title = Yii::$app->name;
$this->params['heading'] = false;

$items = MenuItem::getNavItems();
?>

<div class="site-index" style="font-size: 150%;">
    <?php if ($items) { ?>
        <div class="row row-md-3-clear">
            <?php foreach ($items as $item) {
                if (isset($item['visible']) && $item['visible'] === false) continue;
                ?>
                <div class="col-md-4">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title" style="font-size: 150%;"><?php
                                $label = Html::tag('span', '', ['class' => $item['icon']]) . (isset($item['label']) ? ' ' . $item['label'] : '');
                                echo !empty($item['url']) ? Html::a($label, $item['url']) : $label;
                                ?></h3>
                        </div>
                        <div class="box-body">
                            <?php
                            if (!empty($item['items'])) {
                                foreach ($item['items'] as &$_item) {
                                    unset($_item['items']);
                                    if (empty($_item['url'])) {
                                        $_item = Html::tag('li', $_item['label']);
                                    }
                                }
                                echo Nav::widget([
                                    'options' => ['class' => 'list-unstyled'],
                                    'encodeLabels' => false,
                                    'items' => $item['items'],
                                ]);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>


