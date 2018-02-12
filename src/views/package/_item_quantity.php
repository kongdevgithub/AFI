<?php
/**
 * @var yii\web\View $this
 * @var app\models\Package $model
 */

use app\models\Option;
use yii\helpers\Html;

$controller = Yii::$app->controller->id;
$cache = $model->getCache('_item_quantity.' . $controller);
//$cache=false;
if ($cache === false) {
    $mainPackage = $model;
    if ($model->overflowPackage) {
        $mainPackage = $model->overflowPackage;
    }
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
        foreach ($mainPackage->units as $unit) {
            $total += $unit->quantity;
            ?>
            <tr>
                <td width="15%" align="right"><?php echo $unit->getStatusButton(); ?></td>
                <?php
                if ($controller != 'item') {
                    if ($controller != 'product') {
                        if ($controller != 'job') {
                            ?>
                            <td width="10%"><?php echo Html::a('job-' . $unit->item->product->job->id, $unit->item->product->job->getUrl()); ?></td>
                            <?php
                        }
                        ?>
                        <td width="10%"><?php echo Html::a('product-' . $unit->item->product->id, $unit->item->product->getUrl()); ?></td>
                        <?php
                    }
                    ?>
                    <td width="10%"><?php echo Html::a('item-' . $unit->item->id, $unit->item->getUrl()); ?></td>
                    <?php
                }
                ?>
                <td width="65%">
                    <?php
                    $parts = [
                        $unit->item->product->name,
                        $unit->item->name,
                    ];
                    $sizeHtml = $unit->item->getSizeHtml();
                    if ($sizeHtml) {
                        $parts[] = $sizeHtml;
                    }
                    echo implode(' | ', $parts) . $unit->item->getDescription([
                            'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                            'allowOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
                            'allowComponents' => [0],
                        ]);
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>

        <?php /* ?>
        <tr>
            <td colspan="<?php echo $colspan; ?>" align="right">
                <b><?php echo Yii::t('app', 'Total'); ?></b>
            </td>
            <td align="right">
                <b><?php echo $total; ?></b>
            </td>
        </tr>
        <?php */ ?>

    </table>
    <?php
    $cache = ob_get_clean();
    $model->setCache('_item_quantity.' . $controller, $cache);
}
echo $cache;