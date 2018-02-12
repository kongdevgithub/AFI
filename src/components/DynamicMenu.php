<?php

namespace app\components;

use Yii;
use yii\helpers\Json;

/**
 * DynamicMenu
 */
class DynamicMenu
{
    /**
     * @param $item
     */
    public static function add($item)
    {
        $items = Json::decode(Yii::$app->user->identity->dynamic_menu);
        if (!$items) {
            $items = [];
        }
        $item['active'] = false;
        array_unshift($items, $item);
        while (count($items) > 10) array_pop($items);
        $labels = [];
        foreach ($items as $k => $_item) {
            if (in_array($_item['label'], $labels)) {
                unset($items[$k]);
                continue;
            }
            $labels[] = $_item['label'];
        }
        Yii::$app->user->identity->dynamic_menu = Json::encode($items);
    }

    /**
     * @return array
     */
    public static function getMenuItems()
    {
        $data = Json::decode(Yii::$app->user->identity->dynamic_menu);
        return !empty($data) ? $data : [];
    }

}
