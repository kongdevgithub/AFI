<?php

namespace app\models\form;

use app\components\YdCsv;
use app\models\Company;
use app\models\CompanyRate;
use app\models\CompanyRateOption;
use app\models\Component;
use app\models\ItemType;
use app\models\Option;
use app\models\ProductType;
use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class CompanyRateImportForm
 * @package app\models\form
 *
 */
class CompanyRateImportForm extends Model
{

    /**
     * @var Company
     */
    public $company;

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

        foreach ($data as $row) {
            $productType = $this->getProductType($row['product_type']);
            $itemType = $this->getItemType($row['item_type']);
            $option = $this->getOption($row['option']);
            $component = $this->getComponent($row['component']);

            $companyRateQuery = CompanyRate::find()->notDeleted()
                ->andWhere([
                    'company_id' => $this->company->id,
                    'product_type_id' => $productType->id,
                    'item_type_id' => $itemType->id,
                    'option_id' => $option->id,
                    'component_id' => $component->id,
                ]);
            if ($row['size']) {
                $companyRateQuery->andWhere(['size' => $row['size']]);
            } else {
                $companyRateQuery->andWhere(['or', ['size' => ''], ['size' => null]]);
            }
            $companyRate = $companyRateQuery->one();
            if (!$companyRate) {
                $companyRate = new CompanyRate();
                $companyRate->company_id = $this->company->id;
                $companyRate->product_type_id = $productType->id;
                $companyRate->item_type_id = $itemType->id;
                $companyRate->option_id = $option->id;
                $companyRate->component_id = $component->id;
                $companyRate->size = $row['size'];
            }
            $companyRate->price = $row['price'];
            if (!$companyRate->save()) {
                return false; // TODO handle errors
            }

            $keepOptions = [];
            if (!empty(trim($row['options']))) {
                $options = explode(',', $row['options']);
                foreach ($options as $optionData) {
                    list($optionName, $componentCode) = explode('=', $optionData);
                    $_option = $this->getOption($optionName);
                    $_component = $this->getComponent($componentCode);

                    $companyRateOption = CompanyRateOption::find()->notDeleted()->andWhere([
                        'company_rate_id' => $companyRate->id,
                        'option_id' => $_option->id,
                        'component_id' => $_component->id,
                    ])->one();
                    if (!$companyRateOption) {
                        $companyRateOption = new CompanyRateOption();
                        $companyRateOption->company_rate_id = $companyRate->id;
                        $companyRateOption->option_id = $_option->id;
                        $companyRateOption->component_id = $_component->id;
                        if (!$companyRateOption->save()) {
                            return false; // TODO handle errors
                        }
                    }
                    $keepOptions[] = $companyRateOption->id;
                }
            }
            $cleanupCompanyRateOptions = CompanyRateOption::find()
                ->notDeleted()
                ->andWhere(['company_rate_id' => $companyRate->id])
                ->andWhere(['not', ['in', 'id', $keepOptions]])
                ->all();
            foreach ($cleanupCompanyRateOptions as $companyRateOption) {
                $companyRateOption->delete();
            }
        }

        $transaction->commit();
        return true;
    }

    /**
     * @param string $breadcrumb
     * @return ProductType
     * @throws Exception
     */
    private function getProductType($breadcrumb)
    {
        $productType = false;
        $parent_id = null;
        foreach (explode('>', $breadcrumb) as $crumb) {
            $productType = ProductType::find()
                ->notDeleted()
                ->andWhere(['name' => trim($crumb)])
                ->andWhere(['parent_id' => $parent_id])
                ->one();
            if (!$productType) {
                throw new Exception('Cannot find ProductType with name: ' . $crumb);
            }
            $parent_id = $productType->id;
        }
        if (!$productType) {
            throw new Exception('Cannot find ProductType with breadcrumb: ' . $breadcrumb);
        }
        return $productType;
    }

    /**
     * @param string $name
     * @return ItemType
     * @throws Exception
     */
    private function getItemType($name)
    {
        $itemType = ItemType::find()->notDeleted()->andWhere(['name' => trim($name)])->one();
        if (!$itemType) {
            throw new Exception('Cannot find ItemType with name: ' . $name);
        }
        return $itemType;
    }

    /**
     * @param string $name
     * @return Option
     * @throws Exception
     */
    private function getOption($name)
    {
        $option = Option::find()->notDeleted()->andWhere(['name' => trim($name)])->one();
        if (!$option) {
            throw new Exception('Cannot find Option with name: ' . $name);
        }
        return $option;
    }

    /**
     * @param string $code
     * @return Component
     * @throws Exception
     */
    private function getComponent($code)
    {
        $component = Component::find()->notDeleted()->andWhere(['code' => trim($code)])->one();
        if (!$component) {
            throw new Exception('Cannot find Component with code: ' . $code);
        }
        return $component;
    }

}