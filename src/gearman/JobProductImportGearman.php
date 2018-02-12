<?php

namespace app\gearman;

use app\components\bulk_product\BulkProduct;
use app\components\Helper;
use app\models\Address;
use app\models\Company;
use app\models\ItemToAddress;
use app\models\Job;
use app\models\Log;
use kartik\form\ActiveForm;
use Yii;
use yii\helpers\VarDumper;

/**
 * JobProductImportGearman
 */
class JobProductImportGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        echo $params['id'];
        $job = Job::findOne($params['id']);
        if (!$job) {
            echo 'not found, skipping...';
            return;
        }

        // lock job
        $mutexKey = 'JobProductImportGearman.' . $job->id;
        while (!Yii::$app->mutex->acquire($mutexKey)) {
            Log::log('no lock on ' . $mutexKey . ' - sleeping...', $job);
            sleep(1);
        }

        // process imports
        $importsPending = $job->product_imports_pending;
        $importsComplete = $job->product_imports_complete;

        foreach ($importsPending as $k => $import) {
            echo ' - import ' . ($k + 1) . '/' . count($importsPending);
            $transaction = Yii::$app->dbData->beginTransaction();
            $error = false;
            foreach ($import['data'] as $kk => $row) {
                //echo VarDumper::export($row);
                echo ' - row ' . ($kk + 1) . '/' . count($import['data']);

                // import product
                echo ' - product';
                /** @var BulkProduct $class */
                $class = BulkProduct::className() . '_' . $import['product_type_id'];

                $productForm = $class::getProductForm($job, $row);
                if (!$productForm->save()) {
                    echo ' - error saving ProductForm: ' . Helper::getErrorString($productForm->getAllModels());
                    $importsPending[$k]['data'][$kk]['error'] = $productForm->errorSummary(new ActiveForm());
                    $error = true;
                    break;
                }

                // import delivery mapping
                echo ' - addresses';
                foreach ($row as $columnName => $quantity) {
                    if (substr($columnName, 0, 2) != 'D:' || $quantity < 1) {
                        continue;
                    }
                    $addressName = trim(substr($columnName, 2));
                    $address = $this->getAddress($job, $addressName);
                    foreach ($productForm->product->items as $item) {
                        if ($item->quantity < 1) continue;
                        $itemToAddress = new ItemToAddress();
                        $itemToAddress->item_id = $item->id;
                        $itemToAddress->address_id = $address->id;
                        $itemToAddress->quantity = $quantity;
                        $itemToAddress->save(false);
                    }
                }

            }
            $import['updated'] = date('Y-m-d H:i:s');
            if (!$error) {
                $transaction->commit();
                $import['status'] = 1; // success
            } else {
                $transaction->rollBack();
                $import['status'] = 2; // error
            }
            $importsComplete[] = $import;
        }

        $job->product_imports_pending = null;
        $job->product_imports_complete = $importsComplete;
        $job->save(false);

        // release lock
        Yii::$app->mutex->release($mutexKey);

        echo ' - done!';
    }

    /**
     * @param Job $job
     * @param string $addressName
     * @return Address
     */
    private function getAddress($job, $addressName)
    {
        // find address in job
        $address = Address::find()
            ->notDeleted()
            ->andWhere([
                'model_name' => Job::className(),
                'model_id' => $job->id,
                'type' => Address::TYPE_SHIPPING,
                'name' => $addressName,
            ])
            ->one();
        if ($address) return $address;

        // find address in company
        $companyAddress = Address::find()
            ->notDeleted()
            ->andWhere([
                'model_name' => Company::className(),
                'model_id' => $job->company->id,
                'type' => Address::TYPE_SHIPPING,
                'name' => $addressName,
            ])
            ->one();
        if ($companyAddress) {
            $address = $companyAddress->copy(['Address' => [
                'model_name' => Job::className(),
                'model_id' => $job->id,
            ]]);
            return $address;
        }

        // create a new address
        $address = new Address();
        $address->model_name = Job::className();
        $address->model_id = $job->id;
        $address->type = Address::TYPE_SHIPPING;
        $address->name = $addressName;
        $address->street = '???';
        $address->postcode = '0000';
        $address->state = '???';
        $address->country = '???';
        $address->save(false);

        return $address;
    }


}