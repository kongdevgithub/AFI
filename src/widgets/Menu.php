<?php

namespace app\widgets;

use yii\helpers\Html;

/**
 * Menu
 * @package app\widgets
 */
class Menu extends \yii\widgets\Menu
{
    /**
     * @inheritdoc
     */
    public function renderItem($item)
    {
        if (!isset($item['label'])) $item['label'] = '';
        $icon = isset($item['icon']) ? '<span class="' . $item['icon'] . '"></span> ' : '';
        $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
        $item['label'] = $icon . $label;
        $item['encode'] = false;
        return parent::renderItem($item);
    }

}