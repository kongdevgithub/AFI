<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\Helper;
use app\models\ItemType;
use yii\console\Controller;

/**
 * Class ItemTypeController
 * @package app\commands
 */
class ItemTypeController extends Controller
{


    /**
     * @param bool $force
     */
    public function actionSetColor($force = false)
    {
        $this->stdout('FINDING ITEM TYPES' . "\n");
        $itemTypes = ItemType::find()
            ->notDeleted();
        $count = $itemTypes->count();

        foreach ($itemTypes->all() as $k => $itemType) {
            $this->stdout(CommandStats::stats($k + 1, $count));
            if ($itemType->color && !$force) {
                $this->stdout('has color, skipping...' . "\n");
                continue;
            }
            $itemType->color = Helper::stringToColor(md5(uniqid()));
            $this->stdout('assigning color-' . $itemType->color . ' to itemType-' . $itemType->id . "\n");
            $itemType->save(false);
        }
        $this->stdout('DONE!' . "\n");
    }

}
