<?php

namespace app\controllers;

use app\components\Controller;
use app\components\CopeFreight;
use app\components\MyFreight;
use app\components\PdfManager;
use app\components\ReturnUrl;
use app\models\form\PickupPackageForm;
use app\models\form\PickupPrintForm;
use app\models\form\PickupProgressForm;
use app\models\Log;
use app\models\Pickup;
use app\models\search\PickupSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\PickupController".
 */
class PickupController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Pickup models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PickupSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Creates a new Pickup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Pickup;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickup has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Pickup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickup has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     * Deletes an existing Pickup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickup has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Pickup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pickup the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pickup::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }


    /**
     * @inheritdoc
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Pickup is deleted.'));
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'status';
        $post = Yii::$app->request->post();

        if ($post) {
            if ($model->load($post) && $model->save()) {
                Log::log('updated status', $model->className(), $model->id);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickup has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/pickup/view', 'id' => $model->id]));
            }
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @param $status
     * @return string|\yii\web\Response
     */
    public function actionProgress($status)
    {
        $model = new PickupProgressForm();
        $model->ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : false;
        $model->status = $status;

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            if ($model->ids) {
                foreach ($model->ids as $pickup_id) {
                    Log::log('progress status', Pickup::className(), $pickup_id);
                }
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickups have been progressed.'));
            return $this->redirect(ReturnUrl::getUrl(Url::home()));
        } elseif (!$post) {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('progress', ['model' => $model]);
    }

    /**
     * @param null $id
     * @param bool $confirm
     * @return string
     */
    public function actionPrint($id = null, $confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new PickupPrintForm();
        $model->ids = !empty($post['ids']) ? $post['ids'] : [];
        if ($id) {
            $model->ids[] = $id;
        }

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickups have been printed.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * @param $id
     * @param bool $html
     * @return string
     * @throws Exception
     */
    public function actionPdf($id, $html = false)
    {
        $model = $this->findModel($id);
        if (!$html) {
            $pdf = PdfManager::getPickup($model);
            $filename = 'pickup-' . $model->id . '.pdf';
            if (!$pdf->send($filename, true)) {
                //debug($pdf->getCommand()->getOutput()); die;
                throw new Exception('Could not create PDF: ' . $pdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('pdf', ['model' => $model]);
    }


    /**
     * @param null $id
     * @param bool $confirm
     * @return string
     */
    public function actionMyFreight($id = null, $confirm = false)
    {
        $post = Yii::$app->request->post();
        $ids = isset($post['ids']) ? $post['ids'] : [];
        if ($id) $ids[] = $id;
        if ($confirm) {
            foreach ($ids as $id) {
                $pickup = Pickup::findOne($id);
                if ($pickup) {
                    MyFreight::upload($pickup);
                }
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickups have been uploaded to MyFreight.'));
            return $this->redirect(ReturnUrl::getUrl(['/']));
        }
        return $this->render('my-freight', [
            'ids' => $ids,
        ]);
    }

    /**
     * @param null $id
     * @param bool $confirm
     * @return string
     */
    public function actionCopeFreight($id = null, $confirm = false)
    {
        $post = Yii::$app->request->post();
        $ids = isset($post['ids']) ? $post['ids'] : [];
        if ($id) $ids[] = $id;
        if ($confirm) {
            foreach ($ids as $id) {
                $pickup = Pickup::findOne($id);
                if ($pickup) {
                    CopeFreight::upload($pickup);
                }
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Pickups have been uploaded to Cope.'));
            return $this->redirect(ReturnUrl::getUrl(['/']));
        }
        return $this->render('cope-freight', [
            'ids' => $ids,
        ]);
    }


    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionPackage($id)
    {
        $model = new PickupPackageForm();
        $model->pickup = $this->findModel($id);

        $post = Yii::$app->request->post();

        if ($post) {
            if ($model->load($post) && $model->save()) {
                Log::log('added packages to pickup', $model->pickup->className(), $model->pickup->id);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been assigned to pickup.'));
                return $this->redirect(ReturnUrl::getUrl(['/pickup/view', 'id' => $model->pickup->id]));
            }
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('package', ['model' => $model]);
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
     * @inheritdoc
     */
    public function actionScrapePod($id)
    {
        $model = $this->findModel($id);
        $pod = $model->scrapePod();
        if ($pod) {
            $model->pod_date = $pod;
            $model->save();
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'POD has been scraped.'));
        } else {
            Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'Could not find POD in Tracking URL.'));
        }
        return $this->render('view', ['model' => $model]);
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
