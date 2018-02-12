<?php

namespace app\controllers;

use app\components\Controller;
use app\components\Helper;
use app\components\PdfManager;
use app\models\Attachment;
use app\models\Export;
use app\models\form\ItemPrintForm;
use app\models\form\ItemProgressForm;
use app\models\form\ItemShippingAddressQuantityForm;
use app\models\form\ItemSplitForm;
use app\models\form\ItemStatusForm;
use app\models\Item;
use app\models\ItemToMachine;
use app\models\Job;
use app\models\Log;
use app\models\MachineType;
use app\components\ReturnUrl;
use app\models\Option;
use app\models\search\ItemSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * This is the class for controller "app\controllers\ItemController".
 */
class ItemController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Item models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ItemSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
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
            $export->model_name = ItemSearch::className();
            $export->model_params = Json::encode(Yii::$app->request->post('ItemSearch'));
            if ($export->save()) {
                $export->spoolGearman();
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item export has begun.'));
                return $this->redirect(['/export/view', 'id' => $export->id, 'ru' => ReturnUrl::getRequestToken()]);
            }
        }
        return $this->render('export', [
            'searchParams' => (array)Yii::$app->request->get('ItemSearch'),
        ]);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new Item;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('created item', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
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
            Log::log('updated item', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->splits) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Cannot delete an item that has been split.'));
        } else {
            $model->delete();
            Log::log('deleted item', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been deleted.'));
        }

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * @inheritdoc
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Item is deleted.'));
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionPreviewNotes($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Item is deleted.'));
        }
        return $this->render('preview-notes', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionPreviewNotifications($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Item is deleted.'));
        }
        return $this->render('preview-notifications', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionStatus($id)
    {
        $model = new ItemStatusForm();
        $model->item = $this->findModel($id);
        $model->item->scenario = 'status';
        $model->item->loadDefaultValues();
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Log::log('updated status', $model->item->className(), $model->item->id);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/item/view', 'id' => $model->item->id]));
            }
        } else {
            $model->old_status = $model->item->status;
            $model->new_status = $model->item->getNextStatus();
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @param $status
     * @return string|Response
     * @throws Exception
     */
    public function actionProgress($status = null)
    {
        $model = new ItemProgressForm();
        $model->job_id = isset($_REQUEST['job_id']) ? $_REQUEST['job_id'] : false;
        $model->item_ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : false;
        $model->old_status = $model->getStatus($status);

        $post = Yii::$app->request->post();
        if (!empty($post['ItemProgressForm'])) {
            $model->setAttributes($post);
            if ($model->save()) {
                if ($model->job_id) {
                    Log::log('progress status', Job::className(), $model->job_id);
                }
                if ($model->item_ids) {
                    foreach ($model->item_ids as $item_id) {
                        Log::log('progress status', Item::className(), $item_id);
                    }
                }
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Items have been progressed.'));
                return $this->redirect(ReturnUrl::getUrl($model->job_id ? ['/job/view', 'id' => $model->job_id] : Url::home()));
            }
        } else {
            $item = new Item;
            $item->sendToStatus(null);
            $item->enterWorkflow(explode('/', $model->old_status)[0]);
            $item->status = $model->old_status;
            $item->initStatus();
            $model->new_status = $item->getNextStatus();
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('progress', ['model' => $model]);
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

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction->commit();
            $model->product->resetQuoteGenerated();
            $model->product->job->resetQuoteGenerated(false);
            Log::log('updated quantity', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('quantity', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws Exception
     */
    public function actionSplit($id)
    {
        $model = new ItemSplitForm();
        $model->item = $this->findModel($id);
        $post = Yii::$app->request->post();
        if ($post) {
            if ($model->item->split_id) {
                $item = Item::findOne($model->item->split_id);
                $item->quantity += $model->item->quantity;
                $transaction = Yii::$app->dbData->beginTransaction();
                if (!$item->save(false)) {
                    throw new Exception('Cannot save item-' . $item->id . ': ' . Helper::getErrorString($item));
                }
                $model->item->delete();
                $transaction->commit();

                $model->item->product->refresh();
                $model->item->product->resetQuoteGenerated();
                $model->item->product->job->resetQuoteGenerated(false);
                Log::log('merged item', $model->item);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been merged.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->item->id]));
            } else {
                $model->load($post);
                if ($model->save()) {
                    Log::log('split item', $model->item);
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been split.'));
                    return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->item->id]));
                }
            }
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $model->unit_count = $model->item->quantity * $model->item->product->quantity;
        return $this->render('split', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws Exception
     */
    public function actionSplitParent($id)
    {
        $model = $this->findModel($id);
        $oldParent = $model->split;
        if (!$oldParent) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Item is not in a split or is already the parent.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        }

        $model->split_id = null;
        $model->save(false);

        $oldParent->split_id = $model->id;
        $oldParent->save(false);
        $productToOption = $oldParent->getProductToOption(Option::OPTION_ARTWORK);
        if ($productToOption) {
            $productToOption->item_id = $model->id;
            $productToOption->save(false);
        }

        foreach ($oldParent->splits as $item) {
            $item->split_id = $model->id;
            $item->save(false);
        }

        Log::log('changed item split parent', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been made the new parent of the split.'));
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionFixUnitCount($id)
    {
        $model = $this->findModel($id);
        $model->fixUnitCount();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Unit counts have been fixed.'));
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     *
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('Item')) {
            foreach (Yii::$app->request->post('Item') as $k => $id) {
                $item = Item::findOne($id);
                $item->sort_order = $k;
                $item->save(false);
            }
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
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
                        Log::log('updated artwork', $model);
                        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item artwork has been updated.'));
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
     * @throws \yii\web\HttpException
     */
    public function actionArtworkDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->artwork) {
            $model->artwork->delete();
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item Artwork has been deleted.'));
        }
        Log::log('deleted item artwork', $model);
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }


    /**
     * @param $id
     * @param null $machine_type_id
     * @return string|\yii\web\Response
     */
    public function actionPrinter($id, $machine_type_id = null)
    {
        return $this->actionMachine($id, MachineType::MACHINE_TYPE_PRINTER);
    }

    /**
     * @param $id
     * @param null $machine_type_id
     * @return string|\yii\web\Response
     */
    public function actionMachine($id, $machine_type_id = null)
    {
        $model = $this->findModel($id);

        $itemToMachine = $model->getItemToMachine($machine_type_id);
        if (!$itemToMachine) {
            $itemToMachine = new ItemToMachine();
            $itemToMachine->item_id = $model->id;
        }

        $post = Yii::$app->request->post();
        if ($itemToMachine->load($post) && $itemToMachine->save()) {
            Log::log('updated machine ' . $itemToMachine->machine->machineType->name, $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item machine has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        }

        return $this->render('machine', [
            'model' => $model,
            'itemToMachine' => $itemToMachine,
            'machine_type_id' => $machine_type_id,
        ]);
    }

    /**
     * @param $id
     * @param bool $html
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionProductionPdf($id, $html = false)
    {
        $model = $this->findModel($id);
        if (!$html) {
            $pdf = PdfManager::getItemProduction($model);
            $filename = $model->id . '-production.pdf';
            if (!$pdf->send($filename, true)) {
                throw new Exception('Could not create PDF: ' . $pdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('production-pdf', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionLog($id)
    {
        $model = $this->findModel($id);
        return $this->render('log', ['model' => $model]);
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

    /**
     * @param $id
     * @return string|Response
     */
    public function actionPrint($id)
    {
        $model = new ItemPrintForm();
        $model->item = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Log::log('printed', $model->item);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item has been printed.'));
            }
            return $this->redirect(ReturnUrl::getUrl(['/item/view', 'id' => $model->item->id]));
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        if ($model->item->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Item is deleted.'));
        }
        return $this->render('print', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionShippingAddressQuantity($id)
    {
        $model = new ItemShippingAddressQuantityForm();
        $model->item = $this->findModel($id);

        if (Yii::$app->request->post()) {
            $model->setAttributes(Yii::$app->request->post());
            if ($model->save()) {
                Log::log('updated shipping address quantity', $model->item);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Item Shipping Addresses have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->item->id]));
            }
        }

        return $this->render('shipping-address-quantity', ['model' => $model]);
    }

    /**
     * Finds the Item model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Item the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Item::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
