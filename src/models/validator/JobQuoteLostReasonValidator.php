<?php
namespace app\models\validator;

use app\models\Job;

/**
 * JobQuoteLostReasonValidator
 * @package app\models\validator
 */
class JobQuoteLostReasonValidator extends \yii\validators\RequiredValidator
{

    /**
     * @param Job $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($model->status != 'job/quoteLost') {
            return;
        }
        parent::validateAttribute($model, $attribute);
    }

}