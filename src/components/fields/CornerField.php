<?php

namespace app\components\fields;

use app\components\fields\validators\CornerFieldValidator;
use app\components\quotes\components\BaseComponentQuote;
use app\models\Component;
use kartik\select2\Select2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * CornerField
 */
class CornerField extends ComponentField
{

    /**
     *
     */
    const COMPONENT_TYPE = 6;

    /**
     * @param \app\models\ProductToOption $productToOption
     * @return array
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        $rules[] = [['valueDecoded'], CornerFieldValidator::className()];
        return $rules;
    }

    /**
     * @return array
     */
    public function optsPosition()
    {
        return [
            Yii::t('app', 'Top Left'),
            Yii::t('app', 'Top Right'),
            Yii::t('app', 'Bottom Left'),
            Yii::t('app', 'Bottom Right'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        $positions = $this->optsPosition();

        $query = Component::find()
            ->notDeleted()
            ->andWhere(['component_type_id' => self::COMPONENT_TYPE]);
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded()) {
            $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
        }
        $components = ArrayHelper::map($query->orderBy(['name' => SORT_ASC])->all(), 'id', 'name');

        $fields = [];

        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($components, [
            'id' => "ProductToOptions_{$key}_valueDecoded_component",
            'name' => "ProductToOptions[$key][valueDecoded][component]",
            'value' => isset($productToOption->value['component']) ? $productToOption->value['component'] : '',
            'prompt' => '',
        ])->label(Yii::t('app', 'Component'));

        $value = $productToOption->value;
        $productToOption->value = isset($value['position']) ? $value['position'] : [];
        $fields[] = $form->field($productToOption, 'valueDecoded')->checkboxList($positions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_position",
            'name' => "ProductToOptions[$key][valueDecoded][position]",
        ])->label($productToOption->option->name);
        $productToOption->value = $value;

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
                $positions[] = Yii::t('app', 'All Corners');
            } else {
                foreach ($productToOption->valueDecoded['position'] as $position) {
                    $positions[] = $this->optsPosition()[$position];
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