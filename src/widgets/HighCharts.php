<?php
namespace app\widgets;

use dosamigos\highcharts\HighChartsAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * can be removed once this is closed:
 * https://github.com/2amigos/yii2-highcharts-widget/pull/12
 */
class HighCharts extends \dosamigos\highcharts\HighCharts
{
    /**
     * Registers the script for the plugin
     */
    public function registerClientScript()
    {
        $view = $this->getView();

        $bundle = HighChartsAsset::register($view);
        $id = str_replace('-', '_', $this->options['id']);
        $options = $this->clientOptions;

        if (ArrayHelper::getValue($options, 'chart.options3d.enabled')) {
            $bundle->js[] = YII_DEBUG ? 'highcharts-3d.src.js' : 'highcharts-3d.js';
        }

        if (in_array(ArrayHelper::getValue($options, 'chart.type'), ['gauge', 'solidgauge'])) {
            $bundle->js[] = YII_DEBUG ? 'highcharts-more.src.js' : 'highcharts-more.js';
        }

        foreach ($this->modules as $module) {
            $bundle->js[] = "modules/{$module}";
        }

        if ($theme = ArrayHelper::getValue($options, 'theme')) {
            $bundle->js[] = "themes/{$theme}.js";
        }

        $options = Json::encode($options);

        $view->registerJs(";var highChart_{$id} = new Highcharts.Chart({$options});");
    }
}