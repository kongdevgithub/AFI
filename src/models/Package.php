<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\Helper;
use app\models\workflow\PackageWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use app\components\ReturnUrl;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * This is the model class for table "package".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 *
 * @property \app\models\Address $address
 * @property \app\models\Package[] $overflowPackages
 */
class Package extends base\Package
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [PackageWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [PackageWorkflow::className(), 'afterChangeStatus']);
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
            'defaultWorkflowId' => 'package',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            'cacheRelations' => [
                'pickup',
                'overflowPackage',
                //'units',
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
        $scenarios['status'] = ['status'];
        return $scenarios;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return Html::a('package-' . $this->id, ['/package/view', 'id' => $this->id, 'ru' => ReturnUrl::getToken()], ['class' => 'label label-default']);
    }

    /**
     * @param bool $showInactiveMain
     * @return string
     */
    public function getStatusButtons($showInactiveMain = false)
    {
        return $this->getStatusButton();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddress()
    {
        $relation = Address::find()
            ->orOnCondition([
                'address.model_id' => $this->id,
                'address.model_name' => $this->className(),
            ]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddressSingle()
    {
        $relation = Address::find()
            ->andWhere(['address.model_name' => $this->className()]);
        $relation->multiple = false;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnits()
    {
        return $this->hasMany(Unit::className(), ['package_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('package');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOverflowPackages()
    {
        return $this->hasMany(Package::className(), ['overflow_package_id' => 'id']);
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
        if ($this->address) {
            $relation->orOnCondition([
                'log.model_id' => $this->address->id,
                'log.model_name' => Address::className(),
            ]);
        }
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
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
        if (in_array(Address::className(), $relations)) {
            if ($this->address) {
                $relation->orOnCondition([
                    'audit_trail.model_id' => $this->address->id,
                    'audit_trail.model' => Address::className(),
                ]);
            }
        }
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @inheritdoc
     */
    public function loadDefaultValues($skipIfSet = true)
    {
        parent::loadDefaultValues($skipIfSet);
        if ((!$skipIfSet || $this->cartons === null)) {
            $this->cartons = 1;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getDimensionsLabel()
    {
        $label = [];
        if ($this->packageType) {
            $label[] = $this->packageType->name;
        }
        if ($this->type) {
            $label[] = $this->type;
        }
        if ($this->width || $this->length || $this->height) {
            $size = [];
            if ($this->width) {
                $size[] = $this->width;
            }
            if ($this->length) {
                $size[] = $this->length;
            }
            if ($this->height) {
                $size[] = $this->height;
            }
            $label[] = implode('x', $size);
        }
        if ($this->dead_weight) {
            $label[] = $this->dead_weight . 'kg';
        }
        return implode('<br>', $label);
    }

    /**
     * @return string
     */
    public function getAddressLabel()
    {
        return $this->address ? $this->address->getLabel('<br>') : '';
    }

    /**
     * @return Job|bool
     */
    public function getFirstJob()
    {
        foreach ($this->units as $unit) {
            return $unit->item->product->job;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return 'package-' . $this->id;
    }

    /**
     * @return string
     */
    public function getCartonCountLabel()
    {
        if (!$this->pickup) {
            return '';
        }
        $packages = $this->pickup->getPackages()->orderBy(['package.id' => SORT_ASC])->all();
        foreach ($packages as $k => $package) {
            if ($package->id == $this->id) {
                return ($k + 1) . '/' . count($packages);
            }
        }
        return '';
    }


    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        foreach ($this->units as $unit) {
            $unit->status = 'unit/despatch';
            $unit->package_id = null;
            if (!$unit->save()) {
                throw new Exception('Cannot save unit-' . $unit->id . ': ' . Helper::getErrorString($unit));
            }
        }
        return true;
    }
}
