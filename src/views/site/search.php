<?php
/**
 * @var $this yii\web\View
 * @var $keywords string
 * @var $modelMap array
 * @var $understood bool
 */

use app\components\MenuItem;
use app\widgets\Menu;
use app\widgets\Nav;
use yii\bootstrap\Html;
use yii\helpers\StringHelper;

$this->title = Yii::t('app', 'Search');
$this->params['heading'] = $this->title . ':  <code>' . $keywords . '</code>';
?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Search Results'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        if ($understood) {
            echo Html::tag('p', Yii::t('app', 'Your search term was {keywords}, however no matching record could be found.', [
                'keywords' => ' <code>' . $keywords . '</code>',
            ]));
        } else {
            echo Html::tag('p', Yii::t('app', 'Your search term was {keywords}, however it seems you have searched for a term that could not be understood.', [
                'keywords' => ' <code>' . $keywords . '</code>',
            ]));
        }
        ?>
    </div>
</div>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Yii::t('app', 'Search Help'); ?></h3>
    </div>
    <div class="box-body">
        <?php
        echo Html::tag('p', Yii::t('app', 'Valid search terms are currently:'));
        $items = [];
        $items[] = Yii::t('app', '{code} jump to a specific {class} (to the {page} page)', [
            'code' => '<code>123</code>',
            'class' => '<strong>Job</strong>',
            'page' => '<strong>view</strong>',
        ]);
        $items[] = Yii::t('app', '{code} jump to a specific {class} (to the {page} page)', [
            'code' => '<code>123v4</code>',
            'class' => '<strong>Job</strong>',
            'page' => '<strong>view</strong>',
        ]);
        foreach ($modelMap as $k => $v) {
            $items[] = Yii::t('app', '{code} jump to a specific {class} (to the {page} page)', [
                'code' => '<code>' . $k . '-123</code>',
                'class' => '<strong>' . StringHelper::basename($v['className']) . '</strong>',
                'page' => '<strong>' . StringHelper::basename($v['route']) . '</strong>',
            ]);
        }
        echo Html::ul($items, ['encode' => false]);
        ?>
    </div>
</div>
