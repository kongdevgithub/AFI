<?php
/**
 * @var yii\web\View $this
 * @var app\models\Pickup $model
 * @var bool $showUnits
 */

$showUnits = isset($showUnits) ? $showUnits : true;

$controller = Yii::$app->controller->id;
$cache = $model->getCache('_packages.' . $controller . '.' . $showUnits);
//$cache=false;
if ($cache === false) {
    ob_start();
    ?>
    <table class="table table-condensed table-striped table-nobordered">
        <?php
        $total = 0;
        $colspan = 1;
        if ($controller != 'item') {
            $colspan++;
            if ($controller != 'product') {
                $colspan++;
                if ($controller != 'job') {
                    $colspan++;
                }
            }
        }
        foreach ($model->packages as $package) {
            ?>
            <tr>
                <td width="1%"><?php echo $package->getStatusButton(); ?></td>
                <td width="9%" align="right"><?php echo $package->getLink(); ?></td>
                <td width="4%"><?php echo $package->getCartonCountLabel(); ?></td>
                <td width="18%"><?php echo $package->getAddressLabel(); ?></td>
                <td width="8%"><?php echo $package->getDimensionsLabel(); ?></td>
                <?php if ($showUnits) { ?>
                    <td width="60%">
                        <?= $this->render('/package/_item_quantity', ['model' => $package]) ?>
                    </td>
                <?php } ?>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
    $cache = ob_get_clean();
    $model->setCache('_packages.' . $controller, $cache);
}
echo $cache;