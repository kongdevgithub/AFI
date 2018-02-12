<?php

namespace app\models\form;

use app\models\Package;
use kartik\form\ActiveForm;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * Class PackageDimensionsForm
 * @package app\models\form
 *
 * @property \app\models\Package $package
 */
class PackageDimensionsForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var
     */
    public $package_type_id;

    /**
     * @var
     */
    public $type;

    /**
     * @var
     */
    public $width;

    /**
     * @var
     */
    public $length;

    /**
     * @var
     */
    public $height;

    /**
     * @var
     */
    public $dead_weight;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['package_type_id', 'type', 'width', 'length', 'height', 'dead_weight'], 'required'],
            [['package_type_id', 'width', 'length', 'height', 'dead_weight'], 'integer'],
            [['type'], 'string'],
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

        foreach ($this->ids as $id) {
            $package = Package::findOne($id);

            // save package
            $package->package_type_id = $this->package_type_id;
            $package->type = $this->type;
            $package->width = $this->width;
            $package->length = $this->length;
            $package->height = $this->height;
            $package->dead_weight = $this->dead_weight;
            if (!$package->save(false)) {
                $transaction->rollBack();
                return false;
            }

        }

        $transaction->commit();
        return true;
    }

}