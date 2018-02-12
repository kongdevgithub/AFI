<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Item;
use app\models\ProductTypeToComponent;
use app\models\ProductTypeToItemType;
use app\models\ProductTypeToOption;
use app\components\ReturnUrl;
use app\models\search\ProductTypeToItemTypeSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use kartik\form\ActiveForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\ProductTypeToItemTypeController".
 */
class ProductTypeToItemTypeController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all ProductTypeToItemType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductTypeToItemTypeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single ProductTypeToItemType model.
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', compact('model'));
    }


    /**
     * Deletes an existing ProductTypeToItemType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Item Type has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the ProductTypeToItemType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductTypeToItemType the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductTypeToItemType::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new ProductTypeToItemType;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        $post = Yii::$app->request->post();
        if ($post && isset($_GET['ProductTypeToItemType']['product_type_id'])) {
            $post['ProductTypeToItemType']['product_type_id'] = $_GET['ProductTypeToItemType']['product_type_id'];
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Item Type has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['/product-type/view', 'id' => $model->product_type_id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * @inheritdoc
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Item Type has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['/product-type/view', 'id' => $model->product_type_id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     * Copies an existing ProductTypeToItemType model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCopy($id)
    {
        $modelCopy = $this->findModel($id);

        $model = new ProductTypeToItemType;
        $model->scenario = 'create';
        $model->loadDefaultValues();
        $model->attributes = $modelCopy->attributes;
        $model->name = $model->name . ' (' . Yii::t('app', 'Copy') . ')';
        $model->id = null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            foreach ($modelCopy->productTypeToOptions as $_productTypeToOption) {
                $productTypeToOption = new ProductTypeToOption();
                $productTypeToOption->attributes = $_productTypeToOption->attributes;
                $productTypeToOption->id = null;
                $productTypeToOption->product_type_id = $model->product_type_id;
                if ($productTypeToOption->product_type_to_item_type_id) {
                    $productTypeToOption->product_type_to_item_type_id = $model->id;
                }
                $productTypeToOption->save();
            }

            foreach ($modelCopy->productTypeToComponents as $_productTypeToComponent) {
                $productTypeToComponent = new ProductTypeToComponent();
                $productTypeToComponent->attributes = $_productTypeToComponent->attributes;
                $productTypeToComponent->id = null;
                $productTypeToComponent->product_type_id = $model->product_type_id;
                if ($productTypeToComponent->product_type_to_item_type_id) {
                    $productTypeToComponent->product_type_to_item_type_id = $model->id;
                }
                $productTypeToComponent->save();
            }


            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Item has been copied.'));
            return $this->redirect(['/product-type/view', 'id' => $model->product_type_id, 'ru' => ReturnUrl::getRequestToken()]);
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('copy', compact('model'));
    }

    /**
     *
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('ProductTypeToItemType')) {
            foreach (Yii::$app->request->post('ProductTypeToItemType') as $k => $id) {
                $productTypeToOption = ProductTypeToItemType::findOne($id);
                $productTypeToOption->sort_order = $k;
                $productTypeToOption->save(false);
            }
        }
    }

}
