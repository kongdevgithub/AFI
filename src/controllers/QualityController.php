<?php

namespace app\controllers;

use app\components\Controller;
use app\components\DynamicMenu;
use app\components\EmailManager;
use app\components\freight\Freight;
use app\components\GearmanManager;
use app\components\Helper;
use app\components\PdfManager;
use app\components\quotes\jobs\BaseJobQuote;
use app\components\quotes\products\BaseProductQuote;
use app\components\quotes\products\RateProductQuote;
use app\models\Address;
use app\models\Correction;
use app\models\DearSale;
use app\models\Export;
use app\models\form\AddressPackageCreateForm;
use app\models\form\ItemBulkPackageForm;
use app\models\form\JobArtworkEmailForm;
use app\models\form\JobDeliveryForm;
use app\models\form\JobForm;
use app\models\form\JobInvoiceEmailForm;
use app\models\form\JobPrintForm;
use app\models\form\JobQuoteEmailForm;
use app\models\form\PackageItemForm;
use app\models\form\ShippingAddressImportForm;
use app\models\HubSpotDeal;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\models\Notification;
use app\models\Product;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use app\models\Search;
use app\models\search\JobSearch;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * This is the class for controller "QualityController".
 */
class QualityController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'narrow';
        return $this->render('index');
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionJob($id)
    {
        $job = Job::findOne($id);
        if (!$job) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        $this->layout = 'narrow';
        return $this->render('job', ['job' => $job]);
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionItem($id)
    {
        $item = Item::findOne($id);
        if (!$item) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        $this->layout = 'narrow';
        return $this->render('item', ['item' => $item]);
    }

    /**
     * @param $id
     * @param $quantity
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionCheck($id, $quantity)
    {
        list($type, $id) = explode('-', $id);
        if ($type == 'P2O') {
            $productToOption = ProductToOption::findOne($id);
            $productToOption->checked_quantity = $quantity;
            if (!$productToOption->save()) {
                debug($productToOption->errors);
                die;
            }
            $item_id = $productToOption->item_id;
        } elseif ($type == 'P2C') {
            $productToComponent = ProductToComponent::findOne($id);
            $productToComponent->checked_quantity = $quantity;
            if (!$productToComponent->save()) {
                debug($productToComponent->errors);
                die;
            }
            $item_id = $productToComponent->item_id;
        } else {
            throw new HttpException(404, 'Invalid ID type');
        }
        return $this->redirect(['item', 'id' => $item_id]);
    }

}
