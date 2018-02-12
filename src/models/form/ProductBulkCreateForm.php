<?php

namespace app\models\form;

use app\components\GearmanManager;
use app\components\YdCsv;
use app\models\Job;
use app\models\ProductType;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class ProductBulkCreateForm
 * @package app\models\form
 *
 */
class ProductBulkCreateForm extends Model
{
    /**
     * @var Job
     */
    public $job;

    /**
     * @var ProductType
     */
    public $productType;

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
        if (!$this->upload) {
            return false;
        }
        $data = YdCsv::csvToArray($this->upload->tempName);

        $imports = $this->job->product_imports_pending;
        $imports[] = [
            'product_type_id' => $this->productType->id,
            'data' => $data,
            'status' => 0,
            'errors' => [],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->job->product_imports_pending = $imports;
        $this->job->save(false);

        $this->job->spoolProductImport();

        $transaction->commit();
        return true;
    }

}