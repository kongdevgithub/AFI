<?php

namespace app\components\fields;

use app\components\fields\validators\FinishingFieldValidator;
use app\models\Component;
use app\models\ProductToOption;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * FinishingField
 */
class FinishingField extends ComponentField
{

    /**
     *
     */
    const COMPONENT_TYPE = 4;

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], FinishingFieldValidator::className()];
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
     * @param ProductToOption $productToOption
     * @param ActiveForm $form
     * @param string $key
     * @return string
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

        $label = Yii::t('app', 'Position');

        if (count($components) == 1) {
            $fields[] = Html::activeHiddenInput($productToOption, 'valueDecoded', [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => key($components),
            ]);
            $label = current($components);
        } else {
            $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($components, [
                'id' => "ProductToOptions_{$key}_valueDecoded_component",
                'name' => "ProductToOptions[$key][valueDecoded][component]",
                'value' => isset($productToOption->valueDecoded['component']) ? $productToOption->valueDecoded['component'] : '',
                'prompt' => '',
            ])->label(Yii::t('app', 'Component'));
        }

        $value = $productToOption->valueDecoded;
        $productToOption->valueDecoded = isset($value['position']) ? $value['position'] : [];
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
        ])->label($label);
        $productToOption->valueDecoded = $value;

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
        if (!empty($productToOption->valueDecoded['position'])) {
            if (count($productToOption->valueDecoded['position']) == 4) {
                $positions[] = Yii::t('app', 'All Sides');
            } else {
                foreach ($productToOption->valueDecoded['position'] as $position) {
                    if (isset($this->optsPosition()[$position])) {
                        $positions[] = $this->optsPosition()[$position];
                    }
                }
            }
            return $component->name . ' (' . implode(', ', $positions) . ')';
        }
        return '';
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
        $fields[] = $form->field($productTypeToOption, 'valuesDecoded')->widget(Select2::className(), [
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