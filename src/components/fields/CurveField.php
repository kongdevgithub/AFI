<?php

namespace app\components\fields;

use app\components\Helper;
use app\components\MenuItem;
use app\models\Size;
use app\widgets\JavaScript;
use Yii;
use yii\helpers\Html;

/**
 * CurveField
 */
class CurveField extends BaseField
{

    /**
     * @inheritdoc
     */
    public function rulesProduct($productToOption)
    {
        $rules = parent::rulesProduct($productToOption);
        //$rules[] = [['valueDecoded'], SizeFieldValidator::className()];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fieldProduct($productToOption, $form, $key)
    {
        // staff only!
        if (!Yii::$app->user->can('staff')) return '';

        $query = Size::find();
        if ($productToOption->productTypeToOption && $productToOption->productTypeToOption->getValuesDecoded()) {
            $query->andWhere(['id' => $productToOption->productTypeToOption->getValuesDecoded()]);
        }
        $data = [
            'circle' => Yii::t('app', 'Circle'),
            'cylinder' => Yii::t('app', 'Cylinder'),
        ];
        if ($productToOption->productTypeToOption) {
            $config = $productToOption->productTypeToOption->getConfigDecoded();
            if (isset($config['prevent_circle'])) {
                unset($data['circle']);
            }
            if (isset($config['prevent_cylinder'])) {
                unset($data['cylinder']);
            }
        }
        $directions = [
            'hug' => Yii::t('app', 'Hug'),
            'dive' => Yii::t('app', 'Dive'),
        ];
        $toes = [
            'in' => Yii::t('app', 'In'),
            'out' => Yii::t('app', 'Out'),
        ];

        JavaScript::begin();
        $prefix = '.field-ProductToOptions_' . $key . '_valueDecoded_';
        ?>
        <script>
            // show/hide fields
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_type', function () {
                var $parent = $(this).closest('.table-cell');
                $parent.find('<?= $prefix ?>direction').hide().find(':input').prop('disabled', true);
                $parent.find('<?= $prefix ?>toe').hide().find(':input').prop('disabled', true);
                $parent.find('<?= $prefix ?>diameter').hide().find(':input').prop('disabled', true);
                $parent.find('<?= $prefix ?>length').hide().find(':input').prop('disabled', true);
                $parent.find('<?= $prefix ?>degrees').hide().find(':input').prop('disabled', true);
                if ($(this).val() === 'circle') {
                    $parent.find('<?= $prefix ?>diameter').show().find(':input').prop('disabled', false);
                    $parent.find('<?= $prefix ?>length').show().find(':input').prop('disabled', false);
                }
                if ($(this).val() === 'cylinder') {
                    $parent.find('<?= $prefix ?>direction').show().find(':input').prop('disabled', false);
                    $parent.find('<?= $prefix ?>toe').show().find(':input').prop('disabled', false);
                    $parent.find('<?= $prefix ?>degrees').show().find(':input').prop('disabled', false);
                    $parent.find('<?= $prefix ?>diameter').show().find(':input').prop('disabled', false);
                    $parent.find('<?= $prefix ?>length').show().find(':input').prop('disabled', false);
                }
            });
            $('#ProductToOptions_<?= $key ?>_valueDecoded_type').change();

            // update curve fields
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_diameter', updateCurveLength);
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_length', updateCurveDegrees);
            $(document).on('change', '#ProductToOptions_<?= $key ?>_valueDecoded_degrees', updateCurveLength);

            function updateCurveLength(e) {
                var $parent = $(this).closest('.table-cell');
                var $length = $parent.find('<?= $prefix ?>length input');
                var $diameter = $parent.find('<?= $prefix ?>diameter input');
                var $degrees = $parent.find('<?= $prefix ?>degrees input');
                var type = $parent.find('<?= $prefix ?>type select').val();
                var diameter = parseInt($diameter.val());
                var degrees = (type === 'cylinder') ? parseFloat($degrees.val()) : 360;
                if (diameter && degrees) {
                    $length.val(getCurveLength(diameter, degrees));
                }
            }

            function updateCurveDegrees() {
                var $parent = $(this).closest('.table-cell');
                var $length = $parent.find('<?= $prefix ?>length input');
                var $diameter = $parent.find('<?= $prefix ?>diameter input');
                var $degrees = $parent.find('<?= $prefix ?>degrees input');
                var type = $parent.find('<?= $prefix ?>type select').val();
                var diameter = parseFloat($diameter.val());
                var length = parseFloat($length.val());
                if (diameter && length) {
                    $degrees.val(getCurveDegrees(diameter, length));
                }
            }


            function getCurveCircumference(diameter, degrees) {
                return Math.round(Math.PI * diameter * (degrees / 360));
            }

            function getCurveLength(diameter, degrees) {
                return Math.round(getCurveCircumference(diameter, degrees));
            }

            function getCurveDegrees(diameter, length) {
                return Math.round(360 / (getCurveCircumference(diameter, 360) / length));
            }

        </script>
        <?php
        JavaScript::end();
        $fields = [];

        $value = $productToOption->getValueDecoded();
        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($data, [
            'id' => "ProductToOptions_{$key}_valueDecoded_type",
            'name' => "ProductToOptions[$key][valueDecoded][type]",
            'value' => isset($value['type']) ? $value['type'] : '',
            'prompt' => '',
        ])->label(Yii::t('app', 'Curve Type'))->hint(Yii::t('app', 'For help on curve terminology please {link}.', [
            'link' => Html::a(Yii::t('app', 'click here'), MenuItem::getWikiPageUrl('console:afi4_product_create:curve'), ['target' => '_blank']),
        ]));

        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($directions, [
            'id' => "ProductToOptions_{$key}_valueDecoded_direction",
            'name' => "ProductToOptions[$key][valueDecoded][direction]",
            'value' => isset($value['direction']) ? $value['direction'] : '',
        ])->label(Yii::t('app', 'Direction'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->dropDownList($toes, [
            'id' => "ProductToOptions_{$key}_valueDecoded_toe",
            'name' => "ProductToOptions[$key][valueDecoded][toe]",
            'value' => isset($value['toe']) ? $value['toe'] : '',
        ])->label(Yii::t('app', 'Toe'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_diameter",
            'name' => "ProductToOptions[$key][valueDecoded][diameter]",
            'value' => isset($value['diameter']) ? $value['diameter'] : '',
        ])->label(Yii::t('app', 'Outside Diameter'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_degrees",
            'name' => "ProductToOptions[$key][valueDecoded][degrees]",
            'value' => isset($value['degrees']) ? $value['degrees'] : '360',
        ])->label(Yii::t('app', 'Degrees'))
            ->hint(Yii::t('app', 'The portion of the circle, eg: 90, 180, 270, 360.'));

        $fields[] = $form->field($productToOption, 'valueDecoded')->textInput([
            'id' => "ProductToOptions_{$key}_valueDecoded_length",
            'name' => "ProductToOptions[$key][valueDecoded][length]",
            'value' => isset($value['length']) ? $value['length'] : '',
        ])->label(Yii::t('app', 'Curve Length'))
            ->hint(Yii::t('app', 'Change Width (for hug) or Height (for dive) to be the same as this.'));


        return implode("\n", $fields);
    }


    /**
     * @inheritdoc
     */
    public function attributeValueProduct($productToOption)
    {
        return Helper::getCurveHtml($productToOption->getValueDecoded());
    }


}