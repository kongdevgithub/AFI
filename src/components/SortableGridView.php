<?php

namespace app\components;

use Closure;
use kartik\grid\GridView;
use yii\db\ActiveRecord;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\JuiAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * Class SortableGridView
 * @package app\components
 */
class SortableGridView extends GridView
{
    /**
     * @var array|string
     * The url which is called after an order operation.
     * The format is that of yii\helpers\Url::toRoute.
     * The url will be called with the POST method and the following data:
     * - key    the primary key of the ordered ActiveRecord,
     * - pos    the new, zero-indexed position.
     *
     * Example: ['movie/order-actor', 'id' => 5]
     */
    public $orderUrl;

    /**
     * @var string
     */
    public $sortModel;

    /**
     * @var array
     * The options for the jQuery sortable object.
     * See http://api.jqueryui.com/sortable/ .
     * Notice that the options 'helper' and 'update' will be overwritten.
     * Default: empty array.
     */
    public $sortOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!$this->sortModel) {
            return;
        }

        $classes = isset($this->options['class']) ? $this->options['class'] : '';
        $classes .= ' sortable';
        $this->options['class'] = trim($classes);

        $sortJson = Json::encode(ArrayHelper::merge([
            'forcePlaceholderSize' => true,
            'cursor' => 'move',
            'handle' => '.sortable-handle',
            'helper' => new JsExpression('function(e, ui) {
                ui.children().each(function() {
                   jQuery(this).width(jQuery(this).width());
                });
                return ui;
            }'),
            'start' => new JsExpression('function(e, ui){
                ui.placeholder.height(ui.item.height());
            }'),
            'update' => new JsExpression("function(event, ui) {
                jQuery('#{$this->id}').addClass('sorting');
                jQuery.ajax({
                    type: 'POST',
                    url: '" . Url::toRoute($this->orderUrl) . "',
                    data: $(event.target).sortable('serialize'),
                    //data: {key: ui.item.data('key'),pos: ui.item.index()},
                    complete: function() {
                        jQuery('#{$this->id}').removeClass('sorting');
                    }
                });
            }"),
            'axis' => 'y',
        ], $this->sortOptions));
        $id = $this->getId();

        JuiAsset::register($this->view);
        $this->view->registerJs("jQuery('#{$id} tbody').sortable($sortJson);");

        $this->rowOptions = function ($model, $key, $index) {
            /** @var ActiveRecord $model */
            return ['id' => $this->sortModel . '_' . $model->primaryKey];
        };
    }

}
