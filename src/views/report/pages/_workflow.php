<?php

use app\components\Helper;
use cornernote\workflow\manager\models\Workflow;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var Workflow $workflow
 */

?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $workflow->id; ?></h3>
    </div>
    <div class="box-body">
        <table class="table table-condensed">
            <tbody>
            <?php foreach ($workflow->statuses as $status) { ?>
                <tr>
                    <td width="5%" nowrap="nowrap">
                        <?php
                        if ($workflow->initial_status_id == $status->id) {
                            echo '<span class="glyphicon glyphicon-star"></span> ';
                        }
                        echo Html::tag('strong', $status->label ?: $status->name);
                        echo '<br><small>' . $status->workflow_id . '/' . $status->id . '</small>';
                        ?>
                    </td>
                    <td width="95%">
                        <?= Helper::getStatusButton($status->workflow_id . '/' . $status->id) ?>
                        <?php
                        if ($status->startTransitions) {
                            $transitions = [];
                            foreach ($status->startTransitions as $startTransition) {
                                $transitions[] = Helper::getStatusButton($startTransition->workflow_id . '/' . $startTransition->end_status_id);
                            }
                            echo '<span class="glyphicon glyphicon-chevron-right"></span>&nbsp;&nbsp;';
                            echo implode('&nbsp;', $transitions);
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
