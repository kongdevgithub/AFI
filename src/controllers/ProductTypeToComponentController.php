<?php

namespace app\controllers;

use app\components\Controller;
use app\models\ProductTypeToComponent;
use app\components\ReturnUrl;
use app\models\search\ProductTypeToComponentSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use HttpException;
use Yii;

/**
 * This is the class for controller "app\controllers\ProductTypeToComponentController".
 */
class ProductTypeToComponentController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;


    /**
     * Lists all ProductTypeToComponent models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductTypeToComponentSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single ProductTypeToComponent model.
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
     * Deletes an existing ProductTypeToComponent model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Component has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the ProductTypeToComponent model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ProductTypeToComponent the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProductTypeToComponent::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }


    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new ProductTypeToComponent;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        $post = Yii::$app->request->post();
        if ($post && isset($_GET['ProductTypeToComponent']['product_type_id'])) {
            $post['ProductTypeToComponent']['product_type_id'] = $_GET['ProductTypeToComponent']['product_type_id'];
        }

        if ($model->load($post) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Component has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
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
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Product Type To Component has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     *
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('ProductTypeToComponent')) {
            foreach (Yii::$app->request->post('ProductTypeToComponent') as $k => $id) {
                $productTypeToComponent = ProductTypeToComponent::findOne($id);
                $productTypeToComponent->sort_order = $k;
                $productTypeToComponent->save(false);
            }
        }
    }
}
