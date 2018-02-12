<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\models\workflow\RolloutWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "rollout".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 */
class Rollout extends base\Rollout
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [RolloutWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [RolloutWorkflow::className(), 'afterChangeStatus']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'rollout',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
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
}
