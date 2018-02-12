<?php

namespace app\models\form;

use app\components\Helper;
use app\components\YdCsv;
use app\models\Address;
use app\models\Company;
use app\models\Job;
use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * Class ShippingAddressImportForm
 * @package app\models\form
 *
 */
class ShippingAddressImportForm extends Model
{
    /**
     * @var Job|Company
     */
    public $model;

    /**
     * @var UploadedFile
     */
    public $upload;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['upload'], 'file', 'skipOnEmpty' => true, 'extensions' => 'csv'],
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

        $this->upload = UploadedFile::getInstance($this, 'upload');
        $data = YdCsv::csvToArray($this->upload->tempName);
        $errors = [];
        foreach ($data as $k => $row) {
            $row = $this->cleanRow($row);
            $address = new Address();
            $address->model_name = $this->model->className();
            $address->model_id = $this->model->id;
            $address->type = Address::TYPE_SHIPPING;
            $address->setAttributes($row);
            if (!$address->save()) {
                $errors[$k + 2] = Helper::getErrorString($address);
            }
        }

        if ($errors) {
            $errorList = [];
            foreach ($errors as $row_id => $error) {
                $errorList[] = 'Row ' . $row_id . ': ' . $error;
            }
            $this->addError('upload', Html::ul($errorList));
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    /**
     * @param array $row
     * @return array
     */
    private function cleanRow($row)
    {
        foreach ($row as $k => $col) {
            $row[$k] = trim($col);
        }
        $row['name'] = $this->joinFields('name', $row, ' ');
        $row['street'] = $this->joinFields('street', $row, "\n");
        return $row;
    }

    /**
     * @param $field
     * @param $row
     * @param string $glue
     * @return string
     */
    private function joinFields($field, $row, $glue = '')
    {
        if (isset($row[$field])) {
            return $row[$field];
        }
        $values = [];
        for ($i = 1; $i <= 10; $i++) {
            if (!empty($row[$field . '_' . $i])) {
                $values[] = $row[$field . '_' . $i];
            }
        }
        return implode($glue, $values);
    }

}