<?php

namespace app\widgets;

use yii\helpers\Html;

/**
 * Nav
 * @package app\widgets
 */
class Nav extends \yii\bootstrap\Nav
{

    /**
     * @inheritdoc
     */
    public function renderItem($item)
    {
        if (is_array($item)) {
            if (!isset($item['label'])) $item['label'] = '';
            $icon = isset($item['icon']) ? '<span class="' . $item['icon'] . '"></span> ' : '';
            $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
            $item['label'] = $icon . $label;
            $item['encode'] = false;
        }
        return parent::renderItem($item);
    }

    /**
     * @inheritdoc
     */
    protected function renderDropdown($items, $parentItem)
    {
        foreach ($items as $k => $item) {
            if (!isset($item['label'])) $item['label'] = '';
            $icon = isset($item['icon']) ? '<span class="' . $item['icon'] . '"></span> ' : '';
            $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
            $item['label'] = $icon . $label;
            $item['encode'] = false;
            $items[$k] = $item;
        }
        return parent::renderDropdown($items, $parentItem);
    }

}