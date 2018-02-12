<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\Helper;
use app\models\workflow\UnitWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use mar\eav\behaviors\EavBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * This is the model class for table "unit".
 *
 * @property string $quality_fail_reason
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 */
class Unit extends base\Unit
{

    /**
     * @var bool
     */
    public $mergeExistingUnits = true;

    /**
     * @var bool
     */
    private $_qualityFail;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [UnitWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [UnitWorkflow::className(), 'afterChangeStatus']);
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
            'defaultWorkflowId' => 'unit',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => ['item', 'package'],
        ];
        $behaviors['eav'] = [
            'class' => EavBehavior::className(),
            'modelAlias' => static::className(),
            'eavAttributesList' => [
                'quality_fail_reason' => [
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
        $scenarios = parent::scenarios();
        $scenarios['status'] = ['status', 'quantity', 'quality_fail_reason', 'package_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        // quality_fail_reason when status=qualityFail
        $rules[] = [['quality_fail_reason'], 'string'];
        $rules[] = [['quality_fail_reason'], 'required', 'when' => function ($model) {
            /** @var Unit $model */
            return $model->status && explode('/', $model->status)[1] == 'qualityFail';
        }];

        // no validation on package_id
        foreach ($rules as $k => $rule) {
            $fields = $rule[0];
            foreach ($fields as $kk => $field) {
                if (in_array($field, ['package_id'])) {
                    unset($fields[$kk]);
                }
                $rules[$k][0] = $fields;
            }
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['package_id'] = Yii::t('app', 'Package');
        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // enter correct workflow
        if (strpos($this->status, 'unit/') !== false) {
            $workflow = 'unit-' . Inflector::variablize($this->item->itemType->name);
            if (Yii::$app->workflowSource->getWorkflow($workflow)) {
                $this->sendToStatus(null);
                $this->enterWorkflow($workflow);
            }
        }

        if ($insert && $this->mergeExistingUnits) {
            // merge existing units
            $unit = Unit::find()
                ->notDeleted()
                ->andWhere([
                    'item_id' => $this->item_id,
                    'status' => $this->status,
                    'package_id' => in_array(explode('/', $this->status)[1], ['despatch', 'packed', 'complete']) ? $this->package_id : null,
                ])->one();
            if ($unit) {
                $this->quantity += $unit->quantity;
                $unit->delete();
            }
        }

        // update status
        if ($this->scenario == 'status') {
            // unset package
            if (!in_array(explode('/', $this->status)[1], ['packed', 'complete'])) {
                $this->package_id = null;
            }

            // split units
            if ($this->quantity < $this->oldAttributes['quantity']) {
                // find or create unit, then update quantity
                /** @var Unit $unit */
                $unit = Unit::find()
                    ->notDeleted()
                    ->andWhere([
                        'item_id' => $this->item_id,
                        'package_id' => $this->package_id,
                        'status' => $this->status,
                    ])
                    ->one();
                if (!$unit) {
                    $unit = new Unit();
                    $unit->sendToStatus(null);
                    $unit->enterWorkflow(explode('/', $this->status)[0]);
                    $unit->item_id = $this->item_id;
                    $unit->package_id = $this->package_id;
                    $unit->quantity = 0;
                    $unit->sendToStatus($this->oldAttributes['status']);
                    $unit->status = $this->status;
                    $unit->initStatus();
                }
                $unit->quantity += $this->quantity;
                if (!$unit->save()) {
                    throw new Exception('Cannot save Unit-' . $this->id . ': ' . Helper::getErrorString($unit));
                }
                // leave the remaining units alone
                $this->status = $this->oldAttributes['status'];
                $this->package_id = $this->oldAttributes['package_id'];
                $this->quantity = $this->oldAttributes['quantity'] - $this->quantity;
            }

            // merge existing units
            $unit = Unit::find()
                ->notDeleted()
                ->andWhere([
                    'item_id' => $this->item_id,
                    'package_id' => $this->package_id,
                    'status' => $this->status,
                ])
                ->andWhere(['!=', 'id', $this->id])
                ->one();
            if ($unit) {
                $this->quantity += $unit->quantity;
                $unit->delete();
            }

            // clear old package cache
            $package = Package::findOne($this->oldAttributes['package_id']);
            if ($package) {
                $package->clearCache();
            }

            // flag as quality fail
            if ($this->isAttributeChanged('status') && explode('/', $this->status)[1] == 'qualityFail') {
                $this->_qualityFail = true;
            }

        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->_qualityFail) {
            $this->_qualityFail = null;

            // all this junk to get the workflow and next status
            list($workflow, $status) = explode('/', $this->status);
            $unit = new Unit();
            $unit->sendToStatus(null);
            $unit->enterWorkflow($workflow);
            $unit->item_id = $this->item_id;
            $unit->quantity = $this->quantity;
            $unit->sendToStatus($workflow . '/draft');
            $unit->initStatus();
            $status = $unit->getNextStatus();

            // find or create unit, then update quantity
            /** @var Unit $unit */
            $unit = Unit::find()
                ->notDeleted()
                ->andWhere([
                    'item_id' => $this->item_id,
                    'package_id' => null,
                    'status' => $status,
                ])
                ->one();
            if (!$unit) {
                $unit = new Unit();
                $unit->sendToStatus(null);
                $unit->enterWorkflow($workflow);
                $unit->item_id = $this->item_id;
                $unit->quantity = $this->quantity;
                $unit->status = $status;
                $unit->initStatus();
            }
            if (!$unit->save()) {
                throw new Exception('Cannot save Unit-' . $this->id . ': ' . Helper::getErrorString($unit));
            }

        }
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            '#' . $this->item->product->job->vid . '.i' . $this->item->id . '.u' . $this->id . ': ' . $this->item->name,
            $this->item->product->name,
            $this->item->product->job->name,
            $this->item->product->job->company->name,
        ]);
    }

    /**
     * @param array $check
     * @return array
     */
    public function getChangedAlertEmails($check = [])
    {
        $emails = [];
        $alertStatusList = Correction::getChangedAlertStatusList();
        if ($this->status) {
            $status = explode('/', $this->status)[1];
            if (isset($alertStatusList[$this->status]))
                $emails = ArrayHelper::merge($emails, $alertStatusList[$this->status]);

            if (isset($alertStatusList['unit-*/' . $status]))
                $emails = ArrayHelper::merge($emails, $alertStatusList['unit-*/' . $status]);

            $emails = array_unique($emails);
        }
        return $emails;
    }
}
