<?php

namespace app\modules\goldoc\controllers;

use app\components\ReturnUrl;
use app\models\Attachment;
use app\models\Export;
use app\models\Search;
use app\modules\goldoc\models\form\BulkProductArtworkForm;
use app\modules\goldoc\models\form\BulkProductForm;
use app\modules\goldoc\models\form\BulkProductStatusForm;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\search\ProductSearch;
use app\traits\AccessBehaviorTrait;
use Yii;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * This is the class for controller "ProductController".
 */
class ProductController extends base\ProductController
{
    use AccessBehaviorTrait;


    /**
     * Displays a single Product model.
     *
     * @param integer $id
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Product is deleted.'));
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Product model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update-' . explode('/', $model->status)[1];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }


    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        //$model->scenario = 'status';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('status', ['model' => $model]);
    }

    /**
     * Exports all Item models as CSV.
     * @return mixed
     */
    public function actionExport()
    {
        if (Yii::$app->request->isPost) {
            $export = new Export();
            $export->status = 'pending';
            $export->user_id = Yii::$app->user->id;
            $export->model_name = ProductSearch::className();
            $export->model_params = Json::encode(Yii::$app->request->post('ProductSearch'));
            if ($export->save()) {
                $export->spoolGearman();
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product export has begun.'));
                return $this->redirect(['export/view', 'id' => $export->id, 'ru' => ReturnUrl::getRequestToken()]);
            }
        }
        return $this->render('export', [
            'searchParams' => (array)Yii::$app->request->get('ProductSearch'),
        ]);
    }

    /**
     * Saves the search criteria to be reused.
     * @param null $delete
     * @return mixed
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionSaveSearch($delete = null)
    {
        if ($delete) {
            $search = Search::findOne([
                'id' => $delete,
                'user_id' => Yii::$app->user->id,
            ]);
            if ($search) {
                $search->delete();
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product search has been deleted.'));
            }
            return $this->redirect(ReturnUrl::getUrl(['index']));
        }
        $search = new Search();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $search->name = $post['Search']['name'];
            $search->user_id = Yii::$app->user->id;
            $search->model_name = ProductSearch::className();
            $search->model_params = Json::encode($post['ProductSearch']);
            if ($search->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product search has been saved.'));
                return $this->redirect(ReturnUrl::getUrl(['index', 'ProductSearch' => Json::decode($search->model_params)]));
            }
        } else {
            $search->model_params = Json::encode(Yii::$app->request->get('ProductSearch'));
        }
        return $this->render('save-search', [
            'search' => $search,
        ]);
    }

    /**
     * Copies an existing Product model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCopy($id)
    {
        $modelCopy = $this->findModel($id);
        $model = new Product();
        $model->loadDefaultValues();

        $post = Yii::$app->request->post();
        if ($post) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product has been copied.'));
                return $this->redirect(['product/view', 'id' => $model->id]);
            }
        } else {
            $model->load(['Product' => $modelCopy->attributes]);
            $model->supplier_reference = null;
            $model->supplier_priced = null;
        }

        return $this->render('copy', ['model' => $model, 'modelCopy' => $modelCopy]);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionBulkUpdate()
    {
        $model = new BulkProductForm();

        $post = Yii::$app->request->post();
        $model->setAttributes($post);

        if (!empty($post['confirm'])) {
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Products have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['index']));
            }
        }
        return $this->render('bulk-update', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function actionBulkArtwork()
    {
        $model = new BulkProductArtworkForm();

        $post = Yii::$app->request->post();
        $model->setAttributes($post);

        if (!empty($post['confirm'])) {
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Products artwork have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['index']));
            }
        }
        return $this->render('bulk-artwork', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionBulkDelete()
    {
        $post = Yii::$app->request->post();
        $ids = $post['ids'];

        if (!empty($post['confirm'])) {
            foreach ($ids as $id) {
                $model = Product::findOne($id);
                $model->delete();
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Products have been deleted.'));
            return $this->redirect(ReturnUrl::getUrl(['index']));
        }
        return $this->render('bulk-delete', [
            'ids' => $ids,
        ]);
    }

    /**
     * @param $status
     * @return string|\yii\web\Response
     * @throws \raoul2000\workflow\base\WorkflowException
     */
    public function actionBulkStatus($status = null)
    {
        $model = new BulkProductStatusForm();
        $model->ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : false;
        $model->old_status = $model->getStatus($status);

        $post = Yii::$app->request->post();
        if (!$model->validateIds()) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Cannot handle mixed statuses.'));
        } elseif (!empty($post['BulkProductStatusForm'])) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Products status has been updated.'));
                return $this->redirect(ReturnUrl::getUrl());
            }
        } else {
            $item = new Product;
            $item->sendToStatus(null);
            $item->enterWorkflow(explode('/', $model->old_status)[0]);
            $item->status = $model->old_status;
            $item->initStatus();
            $model->new_status = $item->getNextStatus();
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('bulk-status', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\HttpException
     */
    public function actionArtwork($id)
    {
        $model = $this->findModel($id);

        //$artwork = $model->artwork;
        //if (!$artwork) {
        $artwork = new Attachment();
        $artwork->model_name = $model->className() . '-Artwork';
        $artwork->model_id = $model->id;
        //}

        $post = Yii::$app->request->post();
        if ($artwork->load($post)) {
            if ($model->artwork) {
                $model->artwork->delete();
            }
            $artwork->upload = UploadedFile::getInstance($artwork, 'upload');
            if ($artwork->upload) {
                if ($artwork->upload('artwork-' . uniqid() . '-' . $artwork->upload->name)) {
                    if ($artwork->save()) {
                        $model->clearCache();
                        Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product artwork has been updated.'));
                        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
                    }
                }
            }
        }

        return $this->render('artwork', [
            'model' => $model,
            'artwork' => $artwork,
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\HttpException
     */
    public function actionArtworkDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->artwork) {
            $model->artwork->delete();
            Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Product Artwork has been deleted.'));
        }
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }


    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionTrail($id)
    {
        $model = $this->findModel($id);
        return $this->render('trail', ['model' => $model]);
    }

}
