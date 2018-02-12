<?php

namespace app\controllers;

use app\components\Controller;
use app\models\ProductType;
use app\models\ProductTypeToComponent;
use app\models\ProductTypeToItemType;
use app\models\ProductTypeToOption;
use app\models\search\ProductTypeSearch;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use dmstr\bootstrap\Tabs;
use Yii;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\ProductTypeController".
 */
class ProductTypeController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all ItemType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductTypeSearch;
        $searchModel->parent_id = 0;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        // no pagination
        $dataProvider->pagination = false;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @return mixed
     */
    public function actionPermissions()
    {
        $post = Yii::$app->request->post();
        if (isset($post['ProductType'])) {
            $auth = Yii::$app->authManager;
            foreach ($post['ProductType'] as $_role => $permissions) {
                $role = $auth->getRole($_role);
                $children = [];
                foreach ($auth->getChildren($role->name) as $child) {
                    if (strpos($child->name, '_product-type_') !== false) {
                        $children[$child->name] = $child;
                    }
                }
                foreach (array_keys($permissions) as $_permission) {
                    $permission = $auth->getPermission($_permission);
                    if (isset($children[$permission->name])) {
                        unset($children[$permission->name]);
                    } else {
                        $auth->addChild($role, $permission);
                    }
                }
                foreach ($children as $child) {
                    $auth->removeChild($role, $child);
                }
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type Permissions have been updated.'));
            return $this->redirect(['permissions']);
        }
        return $this->render('permissions');
    }

    /**
     * Displays a single ProductType model.
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
     * Creates a new ProductType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ProductType;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type has been created.'));
            return $this->redirect(['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()]);
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing ProductType model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type has been updated.'));
            return $this->redirect(['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()]);
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     * Copies an existing ProductType model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCopy($id)
    {
        $modelCopy = $this->findModel($id);

        $model = new ProductType;
        $model->scenario = 'create';
        $model->loadDefaultValues();
        $model->attributes = $modelCopy->attributes;
        $model->name = $model->name . ' (' . Yii::t('app', 'Copy') . ')';
        $model->id = null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $productTypeToItemTypeMap = [];
            foreach ($modelCopy->productTypeToItemTypes as $_productTypeToItemType) {
                $productTypeToItemType = new ProductTypeToItemType();
                $productTypeToItemType->attributes = $_productTypeToItemType->attributes;
                $productTypeToItemType->id = null;
                $productTypeToItemType->product_type_id = $model->id;
                $productTypeToItemType->save();
                $productTypeToItemTypeMap[$_productTypeToItemType->id] = $productTypeToItemType->id;
            }

            foreach ($modelCopy->productTypeToOptions as $_productTypeToOption) {
                $productTypeToOption = new ProductTypeToOption();
                $productTypeToOption->attributes = $_productTypeToOption->attributes;
                $productTypeToOption->id = null;
                $productTypeToOption->product_type_id = $model->id;
                if ($productTypeToOption->product_type_to_item_type_id) {
                    $productTypeToOption->product_type_to_item_type_id = $productTypeToItemTypeMap[$productTypeToOption->product_type_to_item_type_id];
                }
                $productTypeToOption->save();
            }

            foreach ($modelCopy->productTypeToComponents as $_productTypeToComponent) {
                $productTypeToComponent = new ProductTypeToComponent();
                $productTypeToComponent->attributes = $_productTypeToComponent->attributes;
                $productTypeToComponent->id = null;
                $productTypeToComponent->product_type_id = $model->id;
                if ($productTypeToComponent->product_type_to_item_type_id) {
                    $productTypeToComponent->product_type_to_item_type_id = $productTypeToItemTypeMap[$productTypeToComponent->product_type_to_item_type_id];
                }
                $productTypeToComponent->save();
            }


            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type has been copied.'));
            return $this->redirect(['view', 'id' => $model->id, 'ru' => ReturnUrl::getRequestToken()]);
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
        if (Yii::$app->request->post('ProductType')) {
            foreach (Yii::$app->request->post('ProductType') as $k => $id) {
                $productType = ProductType::findOne($id);
                $productType->sort_order = $k;
                $productType->save(false);
            }
        }
    }

    /**
     * Deletes an existing ProductType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the ProductType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductType the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductType::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
