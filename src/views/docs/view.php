<?php

/**
 * @var yii\web\View $this
 * @var app\models\Component $model
 * @var string $file
 */


use yii\bootstrap\Nav;
use yii\helpers\Url;

$this->title = Yii::t('app', 'Docs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Docs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $file;
?>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= $file; ?></h3>
        </div>
        <div class="box-body">
            <?php
            $path = Yii::getAlias('@docs/' . $file);
            $markdown = file_get_contents($path);
            $parser = new \cebe\markdown\GithubMarkdown();
            echo $parser->parse($markdown);
            ?>
        </div>
    </div>

<?php if ($file == 'README.md') { ?>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Index'); ?></h3>
        </div>
        <div class="box-body">
            <?php
            $items = [];
            $files = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@docs'));
            foreach ($files as $index => $_file) {
                $name = substr($_file, strrpos($_file, '/') + 1);
                $items[] = ['label' => $name, 'url' => Url::to(['docs/view', 'file' => $name])];
            }
            echo Nav::widget(['items' => $items]);
            ?>
        </div>
    </div>
<?php } ?>