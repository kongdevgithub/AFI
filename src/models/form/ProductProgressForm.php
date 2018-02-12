<?php

namespace app\models\form;

use app\models\Address;
use app\models\Job;
use app\models\Package;
use app\models\Unit;
use Yii;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Class ProductProgressForm
 * @package app\models\form
 *
 */
class ProductProgressForm extends Model
{
    /**
     * @var int
     */
    public $job_id;

    /**
     * @var string
     */
    public $status;

    /**
     * @var string
     */
    public $new_status;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['new_status'], 'required'],
        ];
    }

    /**
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->dbData->beginTransaction();

        $job = Job::findOne($this->job_id);
        if ($job) {
            foreach ($job->products as $product) {
                if ($product->status == $this->status) {
                    $product->status = $this->new_status;
                    if (!$product->save(false)) {
                        $transaction->rollBack();
                        return false;
                    }
                }
            }
        }

        $transaction->commit();
        return true;
    }

}