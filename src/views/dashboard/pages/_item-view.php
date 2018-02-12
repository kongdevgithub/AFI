<?php

use app\models\Item;
use app\models\ItemType;
use app\models\MachineType;
use app\models\Option;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use yii\helpers\Html;

/**
 * @var Item $model
 * @var array $showColumns
 */


//$cacheKey = '/dashboard/pages/_item-view/' . md5(serialize($showColumns));
//$output = $model->getCache($cacheKey);
//if ($output) {
//    echo $output;
//    return;
//}
//ob_start();

$ru = ReturnUrl::getRequestToken() ?: ReturnUrl::getToken();

$outputColumns = [];

if (in_array('sortable', $showColumns) || in_array('machine', $showColumns)) {
    echo '<td>';
    echo '<i class="fa fa-arrows sortable-handle"></i>';
    echo '</td>';
}

if (in_array('options.machine', $showColumns) || in_array('machine', $showColumns)) {
    $link = '';
    if ($model->item_type_id == ItemType::ITEM_TYPE_PRINT) {
        if (Y::user()->can('app_item_printer', ['route' => true])) {
            $link = Html::a('<i class="fa fa-print"></i>', ['/item/printer', 'machine_type_id' => MachineType::MACHINE_TYPE_PRINTER, 'id' => $model->id, 'ru' => $ru], [
                    'class' => 'modal-remote',
                    'title' => Yii::t('app', 'Printer'),
                    'data-toggle' => 'tooltip',
                ]) . ' ';
        }
    }
    $machines = [];
    foreach ($model->itemToMachines as $itemToMachine) {
        $machines[] = Html::tag('strong', $itemToMachine->machine->name) . '<br>' . Yii::$app->formatter->asNtext(trim($itemToMachine->details));
    }
    $outputColumns['machine'] = $link . implode('<hr>', $machines);
}

if (in_array('status', $showColumns)) {
    echo '<td class="text-left">';
    if (in_array('status.checkbox', $showColumns)) {
        echo Html::checkbox('check') . ' ';
    }
    echo $model->getStatusButtons();
    echo '</td>';
}
if (in_array('artwork', $showColumns)) {
    $link = '';
    $artwork = '';
    if ($model->artwork) {
        $thumb = $model->artwork->getFileUrl('100x100');
        $image = $model->artwork->getFileUrl('800x800');
        $artwork = Html::a(Html::img($thumb), $image, ['data-fancybox' => 'gallery-' . $model->artwork->id]);
    } else {
        if (Y::user()->can('app_item_artwork', ['route' => true])) {
            $link = Html::a('<i class="fa fa-upload"></i>', ['/item/artwork', 'id' => $model->id, 'ru' => $ru], [
                'class' => 'modal-remote',
                'title' => Yii::t('app', 'Artwork'),
                'data-toggle' => 'tooltip',
            ]);
        }
    }
    $status = '';
    if (in_array('artwork.status', $showColumns)) {
        if (in_array('artwork.status.checkbox', $showColumns)) {
            $status .= Html::checkbox('check') . ' ';
        }
        $status .= $model->getStatusButtons() . '<br><br>';
    }
    echo '<td class="text-left">';
    echo $status . $link . $artwork;
    echo '</td>';
}
if (in_array('name', $showColumns)) {
    $name = [];
    if (in_array('name.job_name', $showColumns)) {
        $name[] = Html::tag('strong', $model->product->job->name);
    }
    if (in_array('name.job_details', $showColumns)) {
        $initials = $model->product->job->staffRep->initials . ' | ' . $model->product->job->staffCsr->initials;
        $name[] = Html::tag('span', $initials . ' | ' . $model->product->job->company->name);
    }
    if (in_array('name.name', $showColumns)) {
        $name[] = $model->product->name . ' | ' . $model->name;
    }
    if (in_array('name.links', $showColumns)) {
        $links = [];
        if (in_array('name.job_links', $showColumns)) {
            $links[] = 'j:&nbsp;' . Html::a($model->product->job->id, ['//job/view', 'id' => $model->product->job->id]);
        }
        $links[] = 'p:&nbsp;' . Html::a($model->product->id, ['//product/view', 'id' => $model->product->id]);
        $links[] = 'i:&nbsp;' . Html::a($model->id, ['//item/view', 'id' => $model->id]);
        $name[] = '<hr style="margin: 2px 0;">' . Html::tag('small', implode(' | ', $links));
    }
    if (in_array('name.dates', $showColumns)) {
        $dates = [];
        if (in_array('name.job_dates', $showColumns)) {
            $dates[] = 'j:&nbsp;' . Yii::$app->formatter->asDate($model->product->job->prebuild_date);
        }
        if ($model->product->due_date && $model->product->due_date != $model->product->job->due_date) {
            $dates[] = 'p:&nbsp;' . Yii::$app->formatter->asDate($model->product->due_date);
        }
        if ($model->due_date && $model->due_date != $model->product->due_date) {
            $dates[] = 'i:&nbsp;' . Yii::$app->formatter->asDate($model->due_date);
        }
        $link = '';
        //if (Y::user()->can('app_item_due', ['route' => true])) {
        //    $link = ' ' . Html::a('<i class="fa fa-calendar"></i>', ['/item/due', 'id' => $model->id, 'ru' => $ru], [
        //            'class' => 'modal-remote',
        //            'title' => Yii::t('app', 'Due Date'),
        //            'data-toggle' => 'tooltip',
        //        ]);
        //}
        $name[] = Html::tag('small', implode(' ', $dates)) . $link;
    }
    echo '<td>';
    echo implode('<br>', $name);
    echo '</td>';
}
if (in_array('options', $showColumns)) {
    $description = $model->getDescription([
        'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
        'ignoreOptions' => [Option::OPTION_ARTWORK],
        'listOptions' => ['class' => 'list-unstyled'],
    ]);

    $icons = $model->getIcons();

    $area = $model->getArea();
    if ($area) {
        $area = Html::tag('strong', ceil($area) . 'm<sup>2</sup>') . '<br>';
    } else {
        $area = '';
    }

    $perimeter = $model->getPerimeter();
    if ($perimeter) {
        $perimeter = Html::tag('strong', ceil($perimeter) . 'm') . '<br>';
    } else {
        $perimeter = '';
    }

    if ($icons && ($area || $perimeter)) {
        $icons .= ' | ';
    }

    $size = Html::tag('strong', str_replace(' ', '&nbsp;', $model->getSizeHtml())) . '<br>';

    $machine = '';
    if (in_array('options.machine', $showColumns)) {
        $machine = $outputColumns['machine'];
    }

    $artworkNotes = '';
    if (in_array('options.artwork_notes', $showColumns)) {
        $artworkNotes = $model->artwork_notes ? '<br>' . Html::tag('small', Yii::$app->formatter->asNtext($model->artwork_notes)) : '';
    }

    echo '<td class="text-right">';
    echo $icons . $area . $perimeter . $size . $description . $machine . $artworkNotes;
    echo '</td>';
}
if (in_array('description', $showColumns)) {
    echo '<td>';
    echo $model->getDescription([
        'highlightOptions' => [Option::OPTION_SUBSTRATE, Option::OPTION_REFRAME_EXTRUSION],
        'ignoreOptions' => [Option::OPTION_ARTWORK],
        'listOptions' => ['class' => 'list-unstyled'],
    ]);
    echo '</td>';
}
if (in_array('company_id', $showColumns)) {
    echo '<td>';
    echo Html::a($model->product->job->company->name, ['//company/view', 'id' => $model->product->job->company->id]);
    echo '</td>';
}
if (in_array('machine', $showColumns)) {
    echo '<td>';
    echo $outputColumns['machine'];
    echo '</td>';
}
if (in_array('created_at', $showColumns)) {
    echo '<td class="text-center">';
    echo $model->created_at;
    echo '</td>';
}

//$output = ob_get_clean();
//echo $model->setCache($cacheKey, $output);