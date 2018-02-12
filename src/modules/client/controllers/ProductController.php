<?php

namespace app\modules\client\controllers;

use app\models\form\ProductForkQuantityForm;
use app\models\form\ProductForm;
use app\models\Item;
use app\models\ItemType;
use app\models\Job;
use app\models\Log;
use app\models\Product;
use app\models\ProductToComponent;
use app\models\ProductToOption;
use app\models\ProductType;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use Yii;
use kartik\form\ActiveForm;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\ProductController".
 */
class ProductController extends Controller
{

    use AccessBehaviorTrait;

    public $layout = '@app/views/layouts/main';

    /**
     * Creates a new Product model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws HttpException
     */
    public function actionCreate()
    {
        $model = new ProductForm();
        $model->product = new Product;
        $model->product->scenario = 'create';
        $model->product->loadDefaultValues();

        $post = Yii::$app->request->post();

        if (isset($_GET['Product']['job_id'])) {
            $post['Product']['job_id'] = $_GET['Product']['job_id'];
        }
        if (isset($_GET['Product']['product_type_id'])) {
            $post['Product']['product_type_id'] = $_GET['Product']['product_type_id'];
        }
        $job = !empty($post['Product']['job_id']) ? Job::findOne($post['Product']['job_id']) : false;
        $productType = !empty($post['Product']['product_type_id']) ? ProductType::findOne($post['Product']['product_type_id']) : false;

        if (isset($_GET['Product']['product_type_id'])) {
            $model->product->product_type_id = $_GET['Product']['product_type_id'];
            $model->product->complexity = $model->product->productType->complexity;
        }

        // client permissions
        if (!$job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $model->setAttributes($post);
        if ($job && $job->status != 'job/draft') {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Product cannot be added unless Job is in status Draft.'));
        } elseif ($productType && !$productType->checkAccess()) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'You don\'t have permission to create this Product Type.'));
        } elseif (Yii::$app->request->post() && $model->save()) {
            Log::log('created product', $model->product);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->product->id]));
        }

        if (!Yii::$app->request->isPost) {
            if (isset($_GET['Product']['job_id'])) {
                $model->product->job_id = $_GET['Product']['job_id'];
            }
            if (isset($_GET['Product']['product_type_id'])) {
                $model->product->product_type_id = $_GET['Product']['product_type_id'];
                $model->product->name = $model->product->productType->name;
                $model->product->quote_class = $model->product->productType->quote_class;
            }
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', [
            'model' => $model,
            'job' => $job,
            'productType' => $productType,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException
     */
    public function actionUpdate($id)
    {
        $model = new ProductForm();
        $model->product = $this->findModel($id);
        $model->product->scenario = 'update';

        // client permissions
        if (!$model->product->job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $model->setAttributes(Yii::$app->request->post());
        if ($model->product->job->status != 'job/draft') {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Product cannot be updated unless Job is in status Draft.'));
        } elseif ($model->product->productType && !$model->product->productType->checkAccess()) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'You don\'t have permission to update this Product Type.'));
        } elseif (Yii::$app->request->post() && $model->save()) {
            Log::log('updated product', $model->product);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['job/view', 'id' => $model->product->job->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        return $this->render('update', ['model' => $model]);
    }

    /**
     * Copies an existing Product model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException
     */
    public function actionCopy($id)
    {
        $modelCopy = $this->findModel($id);

        $model = new ProductForm();
        $model->product = new Product;
        $model->product->scenario = 'copy';
        $model->product->loadDefaultValues();
        $model->product->attributes = $modelCopy->attributes;
        $model->product->id = null;
        //$model->product->name = $model->product->name . ' (' . Yii::t('app', 'Copy') . ')';

        $post = Yii::$app->request->post();
        $model->setAttributes($post);
        if ($model->product->job->status != 'job/draft') {
            $model->product->addError('job_id', Yii::t('app', 'Cannot copy to a Job unless it is in status Draft.'));
            $post = [];
        }
        if ($model->product->productType && !$model->product->productType->checkAccess()) {
            $model->product->addError('job_id', Yii::t('app', 'You don\'t have permission to copy this Product Type.'));
            $post = [];
        }

        // client permissions
        if (!$model->product->job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if ($post && $model->save()) {
            $modelCopy->resetQuoteGenerated();
            $modelCopy->job->resetQuoteGenerated(false);
            Log::log('created product - copied from product-' . $modelCopy->id, $model->product);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been copied.'));
            return $this->redirect(ReturnUrl::getUrl(['job/view', 'id' => $model->product->job_id]));
        } elseif (!Yii::$app->request->isPost) {
            $items = [];
            foreach ($modelCopy->items as $_item) {
                $item = new Item();
                $item->attributes = $_item->attributes;
                $item->id = 'new' . $item->id;
                $item->product_id = $model->product->id;
                $items[] = $item;
            }
            $model->items = $items;

            $productToOptions = [];
            foreach ($modelCopy->productToOptions as $_productToOption) {
                $productToOption = new ProductToOption();
                $productToOption->attributes = $_productToOption->attributes;
                $productToOption->id = null;
                $productToOption->item_id = 'new' . $productToOption->item_id;
                $productToOption->product_id = $model->product->id;
                $productToOptions[] = $productToOption;
            }
            $model->productToOptions = $productToOptions;

            $productToComponents = [];
            foreach ($modelCopy->productToComponents as $_productToComponent) {
                $productToComponent = new ProductToComponent();
                $productToComponent->attributes = $_productToComponent->attributes;
                $productToComponent->id = null;
                $productToComponent->item_id = 'new' . $productToComponent->item_id;
                $productToComponent->product_id = $model->product->id;
                $productToComponents[] = $productToComponent;
            }
            $model->productToComponents = $productToComponents;
        }

        return $this->render('copy', ['model' => $model, 'modelCopy' => $modelCopy]);
    }

    /**
     * Copies an existing Product model to several others with different quantities.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionForkQuantity($id)
    {
        $model = new ProductForkQuantityForm();
        $model->load(Yii::$app->request->post());
        $model->product = $this->findModel($id);

        if ($model->product->job->status != 'job/draft') {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Quantity cannot be forked unless Job is in status Draft.'));
        } elseif (Yii::$app->request->post() && $model->save()) {
            Log::log('quantity forked', $model->product);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been quantity forked.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->product->id]));
        }

        return $this->render('fork-quantity', compact('model'));
    }

    /**
     * @return int
     */
    public function actionProductToOptionFields()
    {
        //$this->layout = 'blank';
        $productToOption = new ProductToOption();
        $productToOption->loadDefaultValues();
        $productToOption->option_id = $_POST['option_id'];
        ob_start();
        $form = ActiveForm::begin([
            'id' => 'Product',
            'type' => 'horizontal',
            'enableClientValidation' => false,
            'formConfig' => [
                'labelSpan' => 4,
            ],
        ]);
        ob_end_clean();
        return $this->render('_form-product-to-option', [
            'key' => $_POST['key'],
            'itemKey' => isset($_POST['itemKey']) ? $_POST['itemKey'] : false,
            'form' => $form,
            'productToOption' => $productToOption,
            'allowOptionChange' => false,
            'allowOptionRemove' => true,
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionQuantity($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'quantity';

        // client permissions
        if (!$model->job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction->commit();
            $model->resetQuoteGenerated();
            $model->job->resetQuoteGenerated(false);
            Log::log('updated quantity', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('quantity', ['model' => $model]);
    }

    /**
     *
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('Product')) {
            foreach (Yii::$app->request->post('Product') as $k => $id) {
                $product = Product::findOne($id);
                $product->sort_order = $k;
                $product->save(false);
            }
        }
    }

    /**
     * Splits a Product into a Print Product and a Non-Print Product
     *
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionSplit($id)
    {
        $nonPrintProduct = $this->findModel($id);

        $printItems = [];
        $nonPrintItems = [];
        foreach ($nonPrintProduct->items as $k => $item) {
            if (!$item->quantity) continue; // skip no quantity
            if ($item->item_type_id == ItemType::ITEM_TYPE_PRINT) {
                $printItems[$item->id] = $item;
            } else {
                $nonPrintItems[$item->id] = $item;
            }
        }
        if (!$printItems || !$nonPrintItems) {
            Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'Product does not require split.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $nonPrintProduct->id]));
        }

        $transaction = Yii::$app->dbData->beginTransaction();

        $printProduct = $nonPrintProduct->copy(['Product' => ['name' => $nonPrintProduct->name . ' - Prints']]);
        $printProduct->refresh();
        foreach ($printProduct->items as $item) {
            if (in_array($item->id, $nonPrintItems)) {
                $item->quantity = 0;
                $item->save(false);
            }
        }
        $printProduct->resetQuoteGenerated();

        $nonPrintProduct->refresh();
        foreach ($nonPrintProduct->items as $item) {
            if (in_array($item->id, $printItems)) {
                $item->quantity = 0;
                $item->save(false);
            }
        }
        $nonPrintProduct->resetQuoteGenerated();

        $transaction->commit();
        $nonPrintProduct->job->resetQuoteGenerated(false);
        Log::log('split product', $nonPrintProduct);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been split.'));
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $nonPrintProduct->id]));
    }

    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }
        if ($model->job->status != 'job/draft') {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $model->delete();
        Log::log('deleted product', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product has been deleted.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Product model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Product the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Product::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
