<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\fields\BaseField;
use app\components\fields\ComponentField;
use app\components\Helper;
use app\components\quotes\components\BaseComponentQuote;
use app\models\workflow\ItemWorkflow;
use app\modules\goldoc\models\Substrate;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use app\components\ReturnUrl;
use cornernote\softdelete\SoftDeleteBehavior;
use mar\eav\behaviors\EavBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * This is the model class for table "item".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property float $quantity
 * @property float $quote_unit_price
 * @property float $quote_quantity
 * @property float $quote_unit_cost
 * @property float $quote_total_cost
 * @property float $quote_factor
 * @property float $quote_total_price
 * @property float $quote_total_price_unlocked
 * @property float $quote_factor_price
 * @property float $quote_weight
 * @property string $description
 *
 * @property string $artwork_approved_by
 * @property string $change_requested_by
 * @property string $change_request_details
 * @property string $artwork_notes
 * @property Attachment $artwork
 * @property Note[] $notes
 * @property Notification[] $notifications
 * @property Attachment[] $attachments
 * @property Item[] $splits
 */
class Item extends base\Item
{

    /**
     * @var bool
     */
    public $send_email;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [ItemWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [ItemWorkflow::className(), 'afterChangeStatus']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'item',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['product'],
        ];
        $behaviors['eav'] = [
            'class' => EavBehavior::className(),
            'modelAlias' => static::className(),
            'eavAttributesList' => [
                'artwork_approved_by' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'change_requested_by' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'change_request_details' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'artwork_notes' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
            ],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = [
            'default' => ['id', 'name', 'product_id', 'item_type_id', 'split_id', 'quote_class', 'product_type_to_item_type_id', 'quantity', 'supplier_id', 'purchase_order', 'supply_date', 'due_date', 'status', 'sort_order', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'order_at', 'despatch_at', 'complete_at', 'packed_at', 'created_at', 'updated_at'],
            'create' => ['id', 'name', 'product_id', 'item_type_id', 'split_id', 'quote_class', 'product_type_to_item_type_id', 'quantity', 'supplier_id', 'purchase_order', 'supply_date', 'due_date', 'status', 'sort_order', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'order_at', 'despatch_at', 'complete_at', 'packed_at', 'created_at', 'updated_at'],
            'update' => ['id', 'name', 'product_id', 'item_type_id', 'split_id', 'quote_class', 'product_type_to_item_type_id', 'quantity', 'supplier_id', 'purchase_order', 'supply_date', 'due_date', 'status', 'sort_order', 'quote_label', 'quote_unit_price', 'quote_quantity', 'quote_total_price', 'quote_total_price_unlocked', 'quote_unit_cost', 'quote_total_cost', 'quote_factor', 'quote_factor_price', 'quote_weight', 'quote_generated', 'deleted_at', 'production_at', 'order_at', 'despatch_at', 'complete_at', 'packed_at', 'created_at', 'updated_at'],
        ];
        $scenarios['status'] = ['status', 'send_email', 'artwork_notes', 'supplier_id', 'purchase_order', 'supply_date', 'change_requested_by', 'change_request_details'];
        $scenarios['quantity'] = ['quantity'];
        $scenarios['update'][] = 'artwork_notes';
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['quantity'], 'number', 'min' => 0];
        $rules[] = [['send_email', 'artwork_notes'], 'safe'];
        $rules[] = [['quantity'], 'validateQuantity'];

        // no validation on product_id
        foreach ($rules as $k => $rule) {
            $fields = $rule[0];
            foreach ($fields as $kk => $field) {
                if (in_array($field, ['product_id'])) {
                    unset($fields[$kk]);
                }
                $rules[$k][0] = $fields;
            }
        }

        return $rules;
    }

    /**
     * @param $attribute
     */
    public function validateQuantity($attribute)
    {
        if ($this->$attribute == 0 && $this->splits) {
            $this->addError($attribute, Yii::t('app', 'You cannot set quantity to 0 on a split item.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['product_id'] = Yii::t('app', 'Product');
        $attributeLabels['item_type_id'] = Yii::t('app', 'Type');
        $attributeLabels['supplier_id'] = Yii::t('app', 'Supplier');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // enter correct workflow
        if (strpos($this->status, 'item/') !== false) {
            $workflow = 'item-' . Inflector::variablize($this->itemType->name);
            if (Yii::$app->workflowSource->getWorkflow($workflow)) {
                $this->sendToStatus(null);
                $this->enterWorkflow($workflow);
            }
        }

        // update unit quantity
        if (!$this->isNewRecord && $this->quantity != $this->getOldAttribute('quantity')) {
            $this->fixUnitCount();
            //$difference = ($this->quantity - $this->getOldAttribute('quantity')) * $this->product->quantity;
            //$this->changeUnitQuantity($difference);
        }

        // reset change request before approval
        if ($this->isAttributeChanged('status') && $this->status == 'approval') {
            $this->change_requested_by = null;
            $this->change_request_details = null;
        }

        // set the dates
        if (($insert || $this->isAttributeChanged('status')) && $this->status) {
            $date = time();
            $status = explode('/', $this->status)[1];
            if ($status == 'draft') {
                $this->production_at = null;
                $this->despatch_at = null;
                $this->packed_at = null;
                $this->complete_at = null;
            }
            if ($status == 'production') {
                if (!$this->production_at)
                    $this->production_at = $date;
            }
            if ($status == 'despatch') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
            }
            if ($status == 'packed') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
            }
            if ($status == 'complete') {
                if (!$this->production_at)
                    $this->production_at = $date;
                if (!$this->despatch_at)
                    $this->despatch_at = $date;
                if (!$this->packed_at)
                    $this->packed_at = $date;
                if (!$this->complete_at)
                    $this->complete_at = $date;
            }
        }

        // sanitize data
        $this->quote_unit_cost = round($this->quote_unit_cost, 4);
        $this->quote_total_cost = round($this->quote_total_cost, 4);
        $this->quote_unit_price = round($this->quote_unit_price, 4);
        $this->quote_total_price = round($this->quote_total_price, 4);
        $this->quote_factor_price = round($this->quote_factor_price, 8);

        return parent::beforeSave($insert);
    }

    /**
     * @param $quantity
     * @throws Exception
     */
    public function changeUnitQuantity($quantity)
    {
        if (!$quantity) {
            return;
        }
        // add units
        if ($quantity > 0) {
            $unit = Unit::find()->notDeleted()->andWhere([
                'item_id' => $this->id,
                'status' => 'unit-' . Inflector::variablize($this->itemType->name) . '/draft',
            ])->one();
            if (!$unit) {
                $unit = new Unit();
                $unit->item_id = $this->id;
                $unit->status = 'unit/draft';
            }
            $unit->quantity += $quantity;
            if (!$unit->save()) {
                throw new Exception('Cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
            }
        }
        // remove units
        if ($quantity < 0) {
            foreach ($this->units as $unit) {
                $unit->quantity += $quantity;
                $quantity = $unit->quantity;
                if ($quantity < 0) {
                    $unit->quantity = 0;
                }
                if ($unit->quantity == 0) {
                    $unit->delete();
                } elseif (!$unit->save()) {
                    throw new Exception('Cannot save unit ' . Helper::getErrorString($unit));
                }
                if ($quantity >= 0) {
                    break;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // create units
        if ($insert) {
            $unit = new Unit();
            $unit->item_id = $this->id;
            $unit->status = 'unit/draft';
            $unit->quantity = $this->quantity * $this->product->quantity;
            if (!$unit->save()) {
                throw new Exception('Cannot save unit ' . Helper::getErrorString($unit));
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->units as $unit) {
            $unit->delete();
        }
        foreach ($this->productToComponents as $productToComponent) {
            $productToComponent->delete();
        }
        foreach ($this->productToOptions as $productToOption) {
            $productToOption->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * @param array $options
     * @return string
     */
    public function getDescription($options = [])
    {
        $cacheKey = 'getDescription.' . md5(serialize($options));
        $description = $this->getCache($cacheKey);
        if ($description) {
            return $description;
        }

        $description = [
            //$this->itemType->getDescription($this),
        ];

        //if (!$this->checkShippingAddressQuantities()) {
        //    $warning = '<br>' . Html::tag('span', Yii::t('app', 'Delivery quantity does not match!'), ['class' => 'label label-danger']);
        //}

        $listOptions = ArrayHelper::remove($options, 'listOptions', []);
        $highlightOptions = ArrayHelper::remove($options, 'highlightOptions', []);
        $forceOptions = ArrayHelper::remove($options, 'forceOptions', []);
        $ignoreOptions = ArrayHelper::remove($options, 'ignoreOptions', []);
        $allowOptions = ArrayHelper::remove($options, 'allowOptions', []);
        foreach ($this->productToOptions as $productToOption) {
            if (in_array($productToOption->option_id, $ignoreOptions)) {
                continue;
            }
            if (!empty($allowOptions) && !in_array($productToOption->option_id, $allowOptions)) {
                continue;
            }
            $show = false;
            foreach ($forceOptions as $k => $forceOption) {
                if (is_array($forceOption)) {
                    if ($productToOption->option_id == $forceOption['option_id'] && $productToOption->valueDecoded == $forceOption['value']) {
                        $show = true;
                    }
                } else {
                    if ($productToOption->option_id == $forceOption) {
                        $show = true;
                    }
                }
            }
            if (!$productToOption->productTypeToOption || $productToOption->productTypeToOption->describes_item) {
                $show = true;
            }
            if ($show) {
                /** @var BaseField $field */
                $field = new $productToOption->option->field_class;
                $value = $field->nameProduct($productToOption);
                if ($value) {
                    if (in_array($productToOption->option_id, $highlightOptions)) {
                        list($k, $v) = explode(': ', $value);
                        $value = Html::tag('span', $v ?: $value, ['class' => 'label label-default', 'style' => 'color:#fff;background:' . Helper::stringToColor(md5($v))]);
                    } else {
                        $value = Html::tag('div', $value, ['class' => 'small']);
                    }
                    $description[] = $value;
                }
            }
        }

        // goldoc hack
        if ($this->product->product_type_id == ProductType::PRODUCT_TYPE_GC2018) {
            if (in_array(Option::OPTION_SUBSTRATE, $highlightOptions)) {
                $code = explode('-', $this->name);
                if ($code[0] == 'PRINT' && !empty($code[4])) {
                    $substrate = Substrate::findOne(['code' => $code[4]]);
                    $substrate = $substrate ? $substrate->name : $code[4];
                    $description[] = Html::tag('span', $substrate, ['class' => 'label label-default', 'style' => 'color:#fff;background:' . Helper::stringToColor(md5($substrate))]);
                }
            }
        }

        $forceComponents = ArrayHelper::remove($options, 'forceComponents', []);
        $ignoreComponents = ArrayHelper::remove($options, 'ignoreComponents', []);
        $allowComponents = ArrayHelper::remove($options, 'allowComponents', []);
        foreach ($this->productToComponents as $productToComponent) {
            if (in_array($productToComponent->component_id, $ignoreComponents)) {
                continue;
            }
            if (!empty($allowComponents) && !in_array($productToComponent->component_id, $allowComponents)) {
                continue;
            }
            $show = false;
            if (in_array($productToComponent->component_id, $forceComponents)) {
                $show = true;
            }
            if (($productToComponent->productTypeToComponent && $productToComponent->productTypeToComponent->describes_item) || !$productToComponent->productTypeToComponent) {
                if ($productToComponent->quantity != 0 && $productToComponent->quote_quantity != 0) {
                    $show = true;
                }
            }
            if ($show) {
                $description[] = $productToComponent->getName() . ($productToComponent->quote_quantity != 1 ? ' x' . ($productToComponent->quote_quantity * 1) . $productToComponent->component->unit_of_measure : '');
            }
        }

        //$shippingAddressQuantities = $this->getShippingAddressQuantities();
        //if ($shippingAddressQuantities) {
        //    $description[] = Yii::t('app', 'Deliver to') . ': ' . Html::ul($shippingAddressQuantities);
        //}

        $listOptions['encode'] = false;
        return $this->setCache($cacheKey, Html::ul($description, $listOptions));
    }

    /**
     * @return array
     */
    public static function optsItemType()
    {
        static $opts = [];
        if (empty($opts)) {
            $opts = ArrayHelper::map(ItemType::find()->orderBy(['sort_order' => SORT_ASC])->all(), 'id', 'name');
        }
        return $opts;
    }

    /**
     * @param bool $showInactiveMain
     * @param null $includeUnitStatus
     * @return string
     */
    public function getStatusButtons($showInactiveMain = false, $includeUnitStatus = null)
    {
        if (in_array(explode('/', $this->status)[1], ['production', 'despatch', 'packed'])) {
            $quantity = 0;
            $buttons = [];
            foreach ($this->units as $unit) {
                $quantity += $unit->quantity;
            }
            foreach ($this->units as $unit) {
                if ($includeUnitStatus !== null && !in_array(explode('/', $unit->status)[1], $includeUnitStatus)) continue;
                $options = [
                    'progress' => $quantity && count($this->units) > 1 ? ($unit->quantity / $quantity * 100) : 0,
                    'title' => Inflector::humanize(explode('/', $unit->getWorkflowStatus()->getId())[0]) . ' ' . $unit->getWorkflowStatus()->getLabel() . ' (' . $unit->quantity . ')',
                ];
                $buttons[] = $unit->getStatusButton($options);
            }
            $button = '';
            if ($showInactiveMain) {
                $button = $this->getStatusButton(['quantity' => false]) . '&nbsp;';
            }
            return $button . '<div class="btn-group">' . implode('', $buttons) . '</div>';
        }
        return $this->getStatusButton();
    }

    /**
     * @inheritdoc
     */
    public function getStatusButton($options = [])
    {
        if (!isset($options['quantity'])) {
            $options['quantity'] = $this->quantity * $this->product->quantity;
        }
        return parent::getStatusButton($options);
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        $cacheKey = 'getStatusList';
        $statusList = $this->getCache($cacheKey, true);
        if ($statusList) return $statusList;

        $statusList = [];
        if (in_array(explode('/', $this->status)[1], ['production', 'despatch', 'packed'])) {
            foreach ($this->units as $unit) {
                $status = $unit->status;
                $quantity = $unit->quantity;
                $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
            }
        } else {
            $status = $this->status;
            $quantity = $this->quantity * $this->product->quantity;
            $statusList[$status] = isset($statusList[$status]) ? $statusList[$status] + $quantity : $quantity;
        }
        //JobSortHelper::sortStatus($statusList);
        return $this->setCache($cacheKey, $statusList, true);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $url = ['/item/view', 'id' => $this->id, 'ru' => ReturnUrl::getToken()];
        return Html::a('item-' . $this->id, $url, [
            'class' => 'label label-default label-item' . $this->item_type_id,
            'style' => 'color:#fff;background:' . $this->itemType->color,
        ]);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            '#' . $this->product->job->vid . '.i' . $this->id . ': ' . $this->name,
            $this->product->name,
            $this->product->job->name,
            $this->product->job->company->name
        ]);
    }

    /**
     * @param bool $includeName
     * @return array
     */
    public function getSize($includeName = false)
    {
        // get size
        $optionSize = $this->getProductToOption(Option::OPTION_SIZE);
        if (!$optionSize) {
            $_size = $this->product->getSize();
        } else {
            $_size = $optionSize->getValueDecoded();
            if (isset($_size['value'])) {
                $size = Size::findOne($_size['value']);
                if ($size) {
                    if ($includeName) {
                        if ($size->name) {
                            $_size['name'] = $size->name;
                        }
                    }
                    if ($size->width) {
                        $_size['width'] = $size->width;
                    }
                    if ($size->height) {
                        $_size['height'] = $size->height;
                    }
                    if ($size->depth) {
                        $_size['depth'] = $size->depth;
                    }
                }
                unset($_size['value']);
            }
        }

        // curve length
        $optionCurve = $this->product->getProductToOption(Option::OPTION_CURVE);
        if ($optionCurve) {
            $valueDecoded = $optionCurve->valueDecoded;
            if (!empty($valueDecoded['type']) && $valueDecoded['type'] == 'cylinder' && !empty($valueDecoded['degrees']) && $valueDecoded['degrees'] > 0) {
                $dim = $valueDecoded['direction'] == 'hug' ? 'width' : 'height';
                $_size[$dim] = $valueDecoded['length'];
            }
        }

        // cube side
        $optionCubeSide = $this->getProductToOption(Option::OPTION_CUBE_SIDE);
        if ($optionCubeSide) {
            if ($optionCubeSide->valueDecoded == 'Left/Right') {
                $_size['width'] = isset($_size['depth']) ? $_size['depth'] : 0;
            } elseif ($optionCubeSide->valueDecoded == 'Top/Bottom') {
                $_size['height'] = isset($_size['depth']) ? $_size['depth'] : 0;
            }
            unset($_size['depth']);
        }

        // octawall 40mm post width
        if (in_array($this->product_type_to_item_type_id, [397, 416]) && $this->quantity > 0) {
            foreach ($this->product->items as $item) {
                if ($item->product_type_to_item_type_id == 409 && $item->quantity > 0) {
                    $_size['width'] += 38 * min(ceil($item->quantity / $this->quantity), 2);
                }
            }
        }

        // get offset
        $optionOffset = $this->getProductToOption(Option::OPTION_SIZE_OFFSET);
        if ($optionOffset) {
            $_sizeOffset = $optionOffset->getValueDecoded();
            if (isset($_sizeOffset['value'])) {
                $sizeOffset = Size::findOne($_sizeOffset['value']);
                if ($sizeOffset) {
                    if (in_array($sizeOffset->id, [Size::SIZE_SS_CURVE_TOE_IN_OFFSET, Size::SIZE_DS_CURVE_TOE_IN_OFFSET, Size::SIZE_DS_CURVE_TOE_OUT_OFFSET])) {
                        // curve offset
                        $optionCurve = $this->product->getProductToOption(Option::OPTION_CURVE);
                        if ($optionCurve) {
                            $valueDecoded = $optionCurve->valueDecoded;
                            if (!empty($valueDecoded['type']) && $valueDecoded['type'] == 'cylinder' && $valueDecoded['degrees'] > 0) {
                                if (($valueDecoded['toe'] == 'in' && in_array($sizeOffset->id, [Size::SIZE_SS_CURVE_TOE_IN_OFFSET, Size::SIZE_DS_CURVE_TOE_IN_OFFSET]))
                                    || ($valueDecoded['toe'] == 'out' && $sizeOffset->id == Size::SIZE_DS_CURVE_TOE_OUT_OFFSET)
                                ) {
                                    $dim = $valueDecoded['direction'] == 'hug' ? 'width' : 'height';
                                    $_size[$dim] = round(pi() * ($valueDecoded['diameter'] + $sizeOffset->width * 2) * ($valueDecoded['degrees'] / 360));
                                }
                            }
                        }
                    } else {
                        // standard offset
                        if ($sizeOffset->width) {
                            $_size['width'] += $sizeOffset->width;
                        }
                        if ($sizeOffset->height) {
                            $_size['height'] += $sizeOffset->height;
                        }
                        if ($sizeOffset->depth) {
                            $_size['depth'] += $sizeOffset->depth;
                        }
                    }
                }
            }
        }

        return $_size;
    }

    /**
     * @return string
     */
    public function getSizeHtml()
    {
        return Helper::getSizeHtml($this->getSize());
    }

    /**
     * @return float|int
     */
    public function getArea()
    {
        $cacheKey = 'getArea';
        $area = $this->getCache($cacheKey);
        if ($area !== false) return $area;

        $area = 0;
        if ($this->item_type_id == ItemType::ITEM_TYPE_PRINT) {
            $_size = $this->getSize();
            if ($_size && isset($_size['width']) && isset($_size['height'])) {
                $area = ($_size['width'] / 1000) * ($_size['height'] / 1000) * $this->quantity * $this->product->quantity;
            }
        }
        return $this->setCache($cacheKey, $area);
    }

    /**
     * @return float|int
     */
    public function getPerimeter()
    {
        $cacheKey = 'getPerimeter';
        $perimeter = $this->getCache($cacheKey);
        if ($perimeter !== false) return $perimeter;

        $perimeter = 0;
        if ($this->item_type_id == ItemType::ITEM_TYPE_FABRICATION) {
            $_size = $this->getSize();
            if ($_size && isset($_size['width'])) {
                if (isset($_size['height'])) {
                    if (isset($_size['depth'])) {
                        $perimeter = (($_size['width'] / 1000) + ($_size['height'] / 1000) + ($_size['depth'] / 1000)) * 4 * $this->quantity * $this->product->quantity;
                    } else {
                        $perimeter = (($_size['width'] / 1000) + ($_size['height'] / 1000)) * 2 * $this->quantity * $this->product->quantity;
                    }
                } else {
                    $perimeter = ($_size['width'] / 1000) * $this->quantity * $this->product->quantity;
                }
            }
        }
        return $this->setCache($cacheKey, $perimeter);
    }

    /**
     * @param int $option_id
     * @return ProductToOption|bool
     */
    public function getProductToOption($option_id)
    {
        $cacheKey = 'getProductToOption.' . $option_id;
        $productToOptionId = $this->getCache($cacheKey);
        if ($productToOptionId !== false) {
            return $productToOptionId ? ProductToOption::findOne($productToOptionId) : false;
        }
        foreach ($this->productToOptions as $productToOption) {
            if ($productToOption->option_id == $option_id) {
                $this->setCache($cacheKey, $productToOption->id);
                return $productToOption;
            }
        }
        $productToOption = $this->product->getProductToOption($option_id);
        $this->setCache($cacheKey, $productToOption ? $productToOption->id : null);
        return $productToOption;
    }

    /**
     * @param int $component_id
     * @return ProductToComponent|bool
     */
    public function getProductToComponent($component_id)
    {
        $cacheKey = 'getProductToComponent.' . $component_id;
        $productToComponentId = $this->getCache($cacheKey);
        if ($productToComponentId !== false) {
            return $productToComponentId ? ProductToComponent::findOne($productToComponentId) : false;
        }
        foreach ($this->productToComponents as $productToComponent) {
            if ($productToComponent->component_id == $component_id) {
                $this->setCache($cacheKey, $productToComponent->id);
                return $productToComponent;
            }
        }
        $productToComponent = $this->product->getProductToComponent($component_id);
        $this->setCache($cacheKey, $productToComponent ? $productToComponent->id : null);
        return $productToComponent;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSplits()
    {
        return $this->hasMany(Item::className(), ['split_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('split');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToItemType()
    {
        return $this->hasOne(ProductTypeToItemType::className(), ['id' => 'product_type_to_item_type_id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToComponents()
    {
        return $this->hasMany(ProductToComponent::className(), ['item_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductToOptions()
    {
        return $this->hasMany(ProductToOption::className(), ['item_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnits()
    {
        return $this->hasMany(Unit::className(), ['item_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('item');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArtwork()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className() . '-Artwork',
            ]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @param int $machine_type_id
     * @return ItemToMachine|bool
     */
    public function getItemToMachine($machine_type_id)
    {
        foreach ($this->itemToMachines as $itemToMachine) {
            if ($itemToMachine->machine->machine_type_id == $machine_type_id) {
                return $itemToMachine;
            }
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        $relation = Notification::find()
            ->andWhere('notification.deleted_at IS NULL')
            ->orOnCondition([
                'notification.model_id' => $this->id,
                'notification.model_name' => $this->className(),
            ])->orderBy(['created_at' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }


    /**
     * @return query\LogQuery
     */
    public function getLogs()
    {
        $relation = Log::find();
        $relation->orOnCondition([
            'log.model_id' => $this->id,
            'log.model_name' => $this->className(),
        ]);
        $relation->orOnCondition([
            'log.model_id' => ArrayHelper::map($this->getUnits()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Unit::className(),
        ]);
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @param array $relations
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails($relations = [])
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => $this->className(),
        ]);
        if (in_array(Unit::className(), $relations)) {
            /** @var Unit[] $units */
            $units = $this->getUnits()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($units, 'id', 'id'),
                'audit_trail.model' => Unit::className(),
            ]);
        }
        if (in_array(ProductToOption::className(), $relations)) {
            /** @var ProductToOption[] $productToOptions */
            $productToOptions = $this->getProductToOptions()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($productToOptions, 'id', 'id'),
                'audit_trail.model' => ProductToOption::className(),
            ]);
        }
        if (in_array(ProductToComponent::className(), $relations)) {
            /** @var ProductToComponent[] $productToComponents */
            $productToComponents = $this->getProductToComponents()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($productToComponents, 'id', 'id'),
                'audit_trail.model' => ProductToComponent::className(),
            ]);
        }
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     *
     */
    public function resetQuoteGenerated()
    {
        $this->quote_generated = 0;
        if (!$this->save(false)) {
            throw new Exception('Cannot save item-' . $this->id . ': ' . Helper::getErrorString($this));
        }
        foreach ($this->productToComponents as $productToComponent) {
            $productToComponent->resetQuoteGenerated();
        }
        foreach ($this->productToOptions as $productToOption) {
            $productToOption->resetQuoteGenerated();
        }
    }

    /**
     * @param array $attributes
     * @return Item|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $item = new Item();
        $item->loadDefaultValues();
        $item->attributes = $this->attributes;
        $item->id = null;
        if (isset($attributes['Item']['status'])) {
            $item->status = $attributes['Item']['status'];
            $item->initStatus();
        } else {
            $item->status = 'item/draft';
        }
        $allowedAttributes = [
            'product_id',
            'split_id',
            'quantity',
            'quote_unit_price',
            'quote_total_price',
            'quote_unit_cost',
            'quote_total_cost',
            'quote_factor_price',
        ];
        if (!empty($attributes['Item'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Item'])) {
                    $item->$attribute = $attributes['Item'][$attribute];
                }
            }
        }
        if (!$item->save()) {
            throw new Exception('cannot copy Item-' . $this->id . ': ' . Helper::getErrorString($item));
        }
        foreach ($this->productToComponents as $_productToComponent) {
            if (!$_productToComponent->item_id) {
                continue;
            }
            $productToComponent = $_productToComponent->copy([
                'ProductToComponent' => [
                    'product_id' => $item->product_id,
                    'item_id' => $item->id,
                ],
            ]);
        }
        foreach ($this->productToOptions as $_productToOption) {
            if (!$_productToOption->item_id) {
                continue;
            }
            $productToOption = $_productToOption->copy([
                'ProductToOption' => [
                    'product_id' => $item->product_id,
                    'item_id' => $item->id,
                ],
            ]);
        }
        return $item;
    }

    /**
     * @return string
     */
    public function getIcons()
    {
        $cacheKey = 'getIcons';
        $icons = $this->getCache($cacheKey);
        if ($icons !== false) return $icons;

        $icons = [];
        //$icons[] = $this->getUrgentIcon();
        //$icons[] = $this->product->job->getStaffRepIcon();
        //$icons[] = $this->product->job->getStaffCsrIcon();
        $icons[] = $this->getNoteIcon();
        $icons[] = $this->getArtworkNotesIcon();
        //$icons[] = $this->getPackagingMethodIcon();
        $icons[] = $this->getPrebuildIcon();
        //$icons[] = $this->getPrintTagIcon();
        $icons[] = $this->getNotificationIcon();
        foreach ($icons as $k => $v) {
            if (!$v) unset($icons[$k]);
        }
        return $this->setCache($cacheKey, implode(' ', $icons));
    }

    /**
     * @return bool|string
     */
    public function getNoteIcon()
    {
        $icon = $this->getCache('getNoteIcon');
        if ($icon !== false) {
            return $icon;
        }
        $notes = [];
        if ($this->notes) {
            $notes[] = 'item';
        }
        if ($this->product->notes) {
            $notes[] = 'product';
        }
        if ($this->product->job->notes) {
            $notes[] = 'job';
        }
        if ($this->product->job->company->notes) {
            $notes[] = 'company';
        }
        if ($notes) {
            $title = Yii::t('app', 'There are notes in: {notes}', [
                'notes' => implode(' | ', $notes),
            ]);
            $icon = Html::a(Helper::getIcon('note.png', ['title' => $title]), ['/item/preview-notes', 'id' => $this->id], [
                'class' => 'modal-remote',
            ]);
        }
        return $this->setCache('getNoteIcon', $icon ? $icon : null);
    }

    /**
     * @return bool|string
     */
    public function getNotificationIcon()
    {
        $icon = $this->getCache('getNotificationIcon');
        if ($icon !== false) {
            return $icon;
        }
        $notifications = [];
        if ($this->notifications) {
            $notifications[] = 'item';
        }
        if ($this->product->notifications) {
            $notifications[] = 'product';
        }
        if ($this->product->job->notifications) {
            $notifications[] = 'job';
        }
        if ($notifications) {
            $title = Yii::t('app', 'There are notifications in: {notifications}', [
                'notifications' => implode(' | ', $notifications),
            ]);
            $icon = Html::a(Helper::getIcon('flag_red.png', ['title' => $title]), ['/item/preview-notifications', 'id' => $this->id], [
                'class' => 'modal-remote',
            ]);
        }
        return $this->setCache('getNotificationIcon', $icon ? $icon : null);
    }

    /**
     * @return bool|string
     */
    public function getArtworkNotesIcon()
    {
        if (!$this->artwork_notes || $this->artwork) {
            return false;
        }
        return Helper::getIcon('artwork-notes.png', ['title' => Yii::t('app', 'Artwork Notes') . ': ' . $this->artwork_notes]);
    }

    /**
     * @return bool|string
     */
    public function getPrebuildIcon()
    {
        if (!$this->product->prebuild_required) {
            return false;
        }
        return Helper::getIcon('prebuild.png', ['title' => Yii::t('app', 'Prebuild required.')]);
    }

    /**
     *
     */
    public function getPrintTagIcon()
    {
        if ($this->item_type_id != ItemType::ITEM_TYPE_PRINT) {
            return false;
        }
        $substrate = '';
        if ($productToOption = $this->getProductToOption(Option::OPTION_SUBSTRATE)) {
            if ($productToOption->valueDecoded) {
                if ($component = Component::findOne($productToOption->valueDecoded)) {
                    $substrate = ' s:' . $component->code;
                }
            }
        }
        $size = $this->getSize();
        $height = !empty($size['height']) ? ' h:' . $size['height'] : '';
        return Html::img(Helper::getTextImage($this->getTitle() . ' | ' . $height . $substrate), [
            'title' => Yii::t('app', 'Printable name tag.'),
            'width' => 16,
            'height' => 16,
            'data-toggle' => 'tooltip',
        ]);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getMaterials($options = [])
    {
        if ($this->isEmPrint()) {
            return $this->getMaterialsEmPrint($options);
        }
        $models = $this->getCache('getMaterials.' . md5(Json::encode($options)));
        if ($models !== false) return $models;

        $models = [];
        // productToOptions
        foreach ($this->productToOptions as $productToOption) {
            //$field = new $productToOption->option->field_class;
            //if (!$field->attributeProduct($productToOption)) continue;
            //if ($productToOption->quote_total_price == 0) continue;
            if ($productToOption->quote_quantity == 0) continue;

            /** @var ComponentField $field */
            $mrItemQuantity = 1;
            $unitOfMeasure = '';
            $component = false;
            $unitDeadWeight = 0;
            $unitCubicWeight = 0;
            if ($productToOption->option->field_class) {
                /** @var BaseField $field */
                $field = new $productToOption->option->field_class;
                if ($field instanceof ComponentField) {
                    if ($productToOption->quote_class) {
                        /** @var BaseComponentQuote $componentQuote */
                        //$componentQuote = new $productToOption->quote_class;
                        $mrItemQuantity = 1; //$componentQuote->getTotalItems($field->getComponent($productToOption), $this->product->job);
                        $component = $field->getComponent($productToOption);
                        if ($component) {
                            $unitOfMeasure = $component->unit_of_measure;
                            $unitDeadWeight = $component->unit_dead_weight;
                            $unitCubicWeight = $component->unit_cubic_weight;
                        }
                    }
                }
            }
            if (!$component) {
                continue;
            }

            // alter QuantityPriceField to hide prices
            $unitCost = (float)$productToOption->quote_unit_cost;
            $quantity = $productToOption->quote_quantity + 0;
            if ($productToOption->option->field_class == 'app\components\fields\QuantityPriceField') {
                $unitCost = $unitCost * $quantity;
                $quantity = 1;
            }

            $models[] = [
                'id' => 'P2O-' . $productToOption->id,
                'option_id' => $productToOption->option->id,
                'component_id' => $component->id,
                //'update_url' => ['/product-to-option/update', 'id' => $productToOption->id, 'ru' => ReturnUrl::getToken()],
                //'delete_url' => ['/product-to-option/delete', 'id' => $productToOption->id, 'ru' => ReturnUrl::getToken()],
                'code' => $field->attributeLabelProduct($productToOption),
                'name' => $field->attributeValueProduct($productToOption),
                'quote_class' => '<span class="label label-info">' . $productToOption->quote_label . '</span>',
                'mr_cost' => number_format($productToOption->quote_make_ready_cost * $mrItemQuantity, 2) . ($mrItemQuantity > 1 && $productToOption->quote_make_ready_cost > 0 ? '/' . $mrItemQuantity : ''),
                'unit_cost' => $unitCost,
                'quantity' => $quantity,
                'unit_of_measure' => $unitOfMeasure,
                'factor' => $productToOption->quote_factor + 0,
                'total_cost' => (float)$productToOption->quote_total_cost,
                'minimum_cost' => (float)$productToOption->quote_minimum_cost,
                'total_price' => (float)$productToOption->quote_total_price,
                'total_dead_weight' => (float)round($unitDeadWeight * $quantity, 3),
                'total_cubic_weight' => (float)round($unitCubicWeight * $quantity, 3),
                'track_stock' => $component->track_stock,
                'stock_cost' => $component->track_stock ? $unitCost * $quantity : 0,
                'quality_check' => $component->quality_check,
                'quality_code' => $component->quality_code,
                'checked_quantity' => $productToOption->checked_quantity + 0,
            ];
        }
        if (!empty($options['ignoreOptions'])) {
            foreach ($models as $k => $v) {
                if (in_array($v['option_id'], $options['ignoreOptions'])) {
                    unset($models[$k]);
                }
            }
        }

        // productToComponents
        foreach ($this->productToComponents as $productToComponent) {
            //if ($productToComponent->quote_total_price == 0) continue;
            if ($productToComponent->quote_quantity == 0) continue;

            /** @var BaseComponentQuote $componentQuote */
            //$componentQuote = new $productToComponent->quote_class;
            $mrItemQuantity = 1; //$componentQuote->getTotalItems($productToComponent->component, $this->product->job);
            $models[] = [
                'id' => 'P2C-' . $productToComponent->id,
                'component_id' => $productToComponent->component->id,
                //'update_url' => ['/product-to-component/update', 'id' => $productToComponent->id, 'ru' => ReturnUrl::getToken()],
                //'delete_url' => ['/product-to-component/delete', 'id' => $productToComponent->id, 'ru' => ReturnUrl::getToken()],
                'code' => $productToComponent->component->code,
                'name' => $productToComponent->getName(),
                'quote_class' => '<span class="label label-info">' . $productToComponent->quote_label . '</span>',
                'mr_cost' => number_format($productToComponent->quote_make_ready_cost * $mrItemQuantity, 2) . ($mrItemQuantity != 1 && $productToComponent->quote_make_ready_cost > 0 ? '/' . $mrItemQuantity : ''),
                'unit_cost' => (float)$productToComponent->quote_unit_cost,
                'quantity' => $productToComponent->quote_quantity + 0,
                'unit_of_measure' => $productToComponent->component->unit_of_measure,
                'factor' => $productToComponent->quote_factor + 0,
                'total_cost' => (float)$productToComponent->quote_total_cost,
                'minimum_cost' => (float)$productToComponent->quote_minimum_cost,
                'total_price' => (float)$productToComponent->quote_total_price,
                'total_dead_weight' => (float)round($productToComponent->component->unit_dead_weight * $productToComponent->quote_quantity, 3),
                'total_cubic_weight' => (float)round($productToComponent->component->unit_cubic_weight * $productToComponent->quote_quantity, 3),
                'track_stock' => $productToComponent->component->track_stock,
                'stock_cost' => $productToComponent->component->track_stock ? $productToComponent->quote_unit_cost * $productToComponent->quote_quantity : 0,
                'quality_check' => $productToComponent->component->quality_check,
                'quality_code' => $productToComponent->component->quality_code,
                'checked_quantity' => $productToComponent->checked_quantity + 0,
            ];
        }
        if (!empty($options['ignoreComponents'])) {
            foreach ($models as $k => $v) {
                if (in_array($v['component_id'], $options['ignoreComponents'])) {
                    unset($models[$k]);
                }
            }
        }

        return $this->setCache('getMaterials.' . md5(Json::encode($options)), $models);
    }

    /**
     * @param array $options
     * @return array
     */
    public function getMaterialsEmPrint($options = [])
    {
        $models = $this->getCache('getMaterialsEmPrint.' . md5(Json::encode($options)));
        if ($models !== false) return $models;

        $models = [];
        $productToOption = $this->getProductToOption(Option::OPTION_EM_PRINT);
        if ($productToOption) {
            //$field = new $productToOption->option->field_class;
            //if (!$field->attributeProduct($productToOption)) continue;
            //if ($productToOption->quote_total_price == 0) continue;
            if ($productToOption->quote_quantity != 0) {

                /** @var ComponentField $field */
                $mrItemQuantity = 1;
                $unitOfMeasure = '';
                $component = false;
                $unitDeadWeight = 0;
                $unitCubicWeight = 0;
                if ($productToOption->option->field_class) {
                    /** @var BaseField $field */
                    $field = new $productToOption->option->field_class;
                    if ($field instanceof ComponentField) {
                        if ($productToOption->quote_class) {
                            /** @var BaseComponentQuote $componentQuote */
                            //$componentQuote = new $productToOption->quote_class;
                            $mrItemQuantity = 1; //$componentQuote->getTotalItems($field->getComponent($productToOption), $this->product->job);
                            $component = $field->getComponent($productToOption);
                            if ($component) {
                                $unitOfMeasure = $component->unit_of_measure;
                                $unitDeadWeight = $component->unit_dead_weight;
                                $unitCubicWeight = $component->unit_cubic_weight;
                            }
                        }
                    }
                }
                if ($component) {

                    // alter QuantityPriceField to hide prices
                    $unitCost = (float)$productToOption->quote_unit_cost;
                    $quantity = $productToOption->quote_quantity + 0;
                    $unitCost = $unitCost * $quantity;
                    $quantity = 1;

                    $models[] = [
                        'id' => 'P2O-' . $productToOption->id,
                        'component_id' => $component->id,
                        //'update_url' => ['/product-to-option/update', 'id' => $productToOption->id, 'ru' => ReturnUrl::getToken()],
                        //'delete_url' => ['/product-to-option/delete', 'id' => $productToOption->id, 'ru' => ReturnUrl::getToken()],
                        'code' => $field->attributeLabelProduct($productToOption),
                        'name' => $field->attributeValueProduct($productToOption),
                        'quote_class' => '<span class="label label-info">' . $productToOption->quote_label . '</span>',
                        'mr_cost' => number_format($productToOption->quote_make_ready_cost * $mrItemQuantity, 2) . ($mrItemQuantity > 1 && $productToOption->quote_make_ready_cost > 0 ? '/' . $mrItemQuantity : ''),
                        'unit_cost' => $unitCost,
                        'quantity' => $quantity,
                        'unit_of_measure' => $unitOfMeasure,
                        'factor' => $productToOption->quote_factor + 0,
                        'total_cost' => (float)$productToOption->quote_total_cost,
                        'minimum_cost' => (float)$productToOption->quote_minimum_cost,
                        'total_price' => (float)$productToOption->quote_total_price,
                        'total_dead_weight' => (float)round($unitDeadWeight * $quantity, 3),
                        'total_cubic_weight' => (float)round($unitCubicWeight * $quantity, 3),
                        'track_stock' => $component->track_stock,
                    ];
                }
            }
        }

        return $this->setCache('getMaterialsEmPrint.' . md5(Json::encode($options)), $models);
    }

    public function getMaterialCheckTotalCount()
    {
        $count = 0;
        foreach ($this->getMaterials() as $material) {
            if (!$material['quality_check']) continue;
            $count++;
        }
        return $count;
    }


    public function getMaterialCheckedCount()
    {
        $count = 0;
        foreach ($this->getMaterials() as $material) {
            if (!$material['quality_check']) continue;
            if (abs($material['checked_quantity'] - ($material['quantity'] * $this->quantity * $this->product->quantity)) < 0.000001) {
                $count++;
            }
        }
        return $count;
    }

    public function isMaterialChecked()
    {
        return $this->getMaterialCheckTotalCount() == $this->getMaterialCheckedCount();
    }

    /**
     * @throws Exception
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function fixUnitCount()
    {
        $quantity = $this->quantity * $this->product->quantity;
        $done = false;
        foreach ($this->units as $unit) {
            if ($done) {
                $unit->delete();
            } else {
                if ($unit->quantity < 0) {
                    $unit->quantity = $quantity;
                    $unit->save(false);
                    $done = true;
                    $quantity = 0;
                } else {
                    $quantity -= $unit->quantity;
                    if ($quantity <= 0) {
                        $unit->quantity += $quantity;
                        if (!$unit->save(false)) {
                            throw new Exception('cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
                        }
                        $done = true;
                        $quantity = 0;
                    }
                }
            }
        }
        if ($quantity) {
            foreach ($this->units as $unit) {
                $unit->quantity += $quantity;
                $quantity = 0;
                if (!$unit->save(false)) {
                    throw new Exception('cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
                }
                break;
            }
        }
        if ($quantity) {
            $unit = new Unit;
            $unit->item_id = $this->id;
            $unit->quantity = $quantity;
            if (!$unit->save(false)) {
                throw new Exception('cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
            }
        }
    }

    /**
     * @return bool
     */
    public function checkUnitCount()
    {
        $quantity = 0;
        foreach ($this->units as $unit) {
            $quantity += $unit->quantity;
        }
        return $quantity == $this->quantity * $this->product->quantity;
    }

    /**
     * @return bool
     */
    public function checkShowSize()
    {
        if ($this->getProductToOption(Option::OPTION_CUBE_SIDE)) {
            return true;
        }
        if ($sizeOffset = $this->getProductToOption(Option::OPTION_SIZE_OFFSET)) {
            $return = true;
            if ($optionCurve = $this->getProductToOption(Option::OPTION_CURVE)) {
                if (empty($optionCurve->valueDecoded['type']) || $optionCurve->valueDecoded['type'] != 'cylinder') {
                    $return = false;
                }
            }
            if ($return) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return MachineType[]
     */
    public function getMachineTypes()
    {
        $machineTypes = [];
        if (in_array($this->item_type_id, [ItemType::ITEM_TYPE_PRINT, ItemType::ITEM_TYPE_EM_PRINT])) {
            if (!$this->isBlankPrint()) {
                $machineTypes[] = MachineType::findOne(1);
            }
        }
        return $machineTypes;
    }

    /**
     * @return bool
     */
    public function isBlankPrint()
    {
        if ($this->item_type_id == ItemType::ITEM_TYPE_PRINT) {
            if ($this->product->product_type_id == 142) {
                return false; // GOLDOC
            }
            $printer = $this->getProductToOption(Option::OPTION_PRINTER);
            if (!$printer || !$printer->valueDecoded || $printer->valueDecoded == Component::COMPONENT_BLANK) {
                return true;
            }
        }
        return false;
    }


    /**
     * @return bool
     */
    public function isEmPrint()
    {
        if ($this->item_type_id == ItemType::ITEM_TYPE_EM_PRINT) {
            $optionEmPrint = $this->getProductToOption(Option::OPTION_EM_PRINT);
            if ($optionEmPrint && $optionEmPrint->valueDecoded) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $check
     * @return array
     */
    public function getChangedAlertEmails($check = [])
    {
        $emails = [];
        $alertStatusList = Correction::getChangedAlertStatusList();
        $status = explode('/', $this->status)[1];

        if (isset($alertStatusList[$this->status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList[$this->status]);

        if (isset($alertStatusList['item-*/' . $status]))
            $emails = ArrayHelper::merge($emails, $alertStatusList['item-*/' . $status]);

        if (in_array($status, ['production', 'prebuild', 'despatch'])) {
            foreach ($this->units as $unit) {
                $emails = ArrayHelper::merge($emails, $unit->getChangedAlertEmails());
            }
        }

        $emails = array_unique($emails);
        return $emails;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->quantity === null)) {
            $this->quantity = 0;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        return $this->itemType->virtual;
    }

    /**
     * @return array
     */
    public function getShippingAddressQuantities()
    {
        $shippingAddresses = [];
        $total = 0;
        foreach ($this->itemToAddresses as $itemToAddress) {
            if (!$itemToAddress->quantity || $itemToAddress->address->deleted_at) continue;
            $shippingAddresses[] = 'x' . $itemToAddress->quantity . ' - ' . $itemToAddress->address->name;
            $total += $itemToAddress->quantity;
        }
        return $shippingAddresses;
    }

    /**
     * @return bool
     */
    public function checkShippingAddressQuantities()
    {
        $total = 0;
        foreach ($this->itemToAddresses as $itemToAddress) {
            if (!$itemToAddress->quantity || $itemToAddress->address->deleted_at) continue;
            $total += $itemToAddress->quantity;
        }
        return $total == $this->quantity * $this->product->quantity;
    }

    /**
     * @return float
     */
    public function getStockCost()
    {
        $stockCost = $this->getCache('getStockCost');
        if ($stockCost !== false) {
            return $stockCost;
        }
        $stockCost = 0;
        foreach ($this->getMaterials() as $material) {
            $stockCost += $material['stock_cost'];
        }
        return $this->setCache('getStockCost', $stockCost);
    }

}
