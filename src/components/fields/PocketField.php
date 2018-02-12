<?php

namespace app\components\fields;

use app\components\fields\validators\PocketFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * PocketField
 */
class PocketField extends ComponentField
{

    /**
     *
     */
    const COMPONENT_TYPE = 5;

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], PocketFieldValidator::className()];
        return $rules;
    }

    /**
     * @return array
     */
    public function optsPosition()
    {
        return [
            Yii::t('app', 'Top'),
            Yii::t('app', 'Bottom'),
            Yii::t('app', 'Left'),
            Yii::t('app', 'Right'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $positions = $this->optsPosition();

        $query = Component::find()
            ->andWhere(['component_type_id' => self::COMPONENT_TYPE]);
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded()) {
            $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
        }
        $components = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

        $fields = [];

        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($components, [
            'id' => "ProductToOptions_{$key}_valueDecoded_component",
            'name' => "ProductToOptions[$key][valueDecoded][component]",
            'value' => isset($productToOption->valueDecoded['component']) ? $productToOption->valueDecoded['component'] : '',
            'prompt' => '',
        ])->label(Yii::t('app', 'Component'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
            'value' => isset($productToOption->valueDecoded['position']) ? $productToOption->valueDecoded['position'] : '',
        ])->label($productToOption->option->name);

        $fields[] = $form->field($productToOption, 'valueDecoded', [
            'addon' => [
                'append' => ['content' => 'mm'],
            ]
        ])->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_size",
            'name' => "ProductToOptions[$key][valueDecoded][size]",
            'value' => isset($productToOption->valueDecoded['size']) ? $productToOption->valueDecoded['size'] : '',
            'prompt' => '',
        ])->label(Yii::t('app', 'Size'));

        return implode("\n", $fields);
    }

    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        $component = $this->getComponent($productToOption);
        if (!$component) {
            return '';
        }
        $positions = [];
        foreach ($productToOption->value['position'] as $position) {
            $positions[] = $this->optsPosition()[$position];
        }
        return $component->name . ' ' . $productToOption->value['size'] . ' mm' . ' (' . implode(', ', $positions) . ')';
    }

    /**
     * @inheritdoc
     */
    public function fieldProductType($productTypeToOption, $form)
    {
        $select2Options = [
            'multiple' => true,
            'theme' => 'krajee',
            'placeholder' => '',
            'language' => 'en-US',
            'width' => '100%',
            //'allowClear' => true,
        ];
        $query = Component::find()->andWhere(['component_type_id' => self::COMPONENT_TYPE])->orderBy(['name' => SORT_ASC]);
        $data = [];
        foreach ($query->all() as $row) {
            $data[$row->id] = $row->name . ' (' . $row->code . ')';
        }
        $fields = [];
        $fields[] = $form->field($productTypeToOption, 'values')->widget(Select2::className(), [
            'model' => $productTypeToOption,
            'attribute' => 'values',
            'data' => $data,
            'options' => $select2Options,
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ]);
        return implode(' ', $fields);
    }

    /**
     * @inheritdoc
     */
    public function getComponent($productToOption)
    {
        if (!$this->_component) {
            if (!empty($productToOption->value['component'])) {
                $this->_component = Component::findOne($productToOption->value['component']);
            }
        }
        return $this->_component;
    }

}