<?php

namespace app\controllers;

use app\components\Controller;
use app\components\PdfManager;
use app\models\form\ItemArtworkApprovalForm;
use app\models\form\JobArtworkApprovalForm;
use app\models\form\JobQuoteApprovalForm;
use app\models\Item;
use app\models\Job;
use app\components\ReturnUrl;
use Imagick;
use kartik\mpdf\Pdf;
use Yii;
use yii\base\Exception;
use yii\helpers\Inflector;
use yii\web\HttpException;

/**
 * This is the class for controller "ApprovalController".
 */
class ApprovalController extends Controller
{

    /**
     * @var string
     */
    public $layout = '@app/views/layouts/narrow';

    /**
     * @return string
     */
    public function actionNotAvailable()
    {
        return $this->render('not-available');
    }

    /**
     * @param $id
     * @param $key
     * @return string
     */
    public function actionQuote($id, $key)
    {
        $model = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        return $this->render('quote', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $key
     * @return string
     */
    public function actionQuoteApproval($id, $key)
    {
        $model = new JobQuoteApprovalForm();
        $model->job = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
            //Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Quote has been accepted!'));
            return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->job->id]));
        }
        return $this->render('quote-approval', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $key
     * @return string
     */
    public function actionQuoteArtwork($id, $key)
    {
        $model = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        return $this->render('quote-artwork', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $key
     * @return \yii\web\Response
     * @throws Exception
     */
    public function actionQuotePdf($id, $key)
    {
        $model = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        $quotePdf = PdfManager::getJobQuote($model);
        $filename = 'quote_' . Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->id . '.pdf';
        if (!$quotePdf->send($filename, true)) {
            throw new Exception('Could not create PDF: ' . $quotePdf->getError());
        }
    }

    /**
     * @param $id
     * @param $key
     * @return string
     */
    public function actionArtwork($id, $key)
    {
        $model = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        return $this->render('artwork', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $key
     * @return \yii\web\Response
     * @throws Exception
     */
    public function actionArtworkPdf($id, $key)
    {
        $model = $this->findJobModel($id, $key);
        if (!$model) {
            return $this->redirect(['not-available']);
        }
        $artworkPdf = PdfManager::getJobArtwork($model);
        $filename = 'artwork_' . Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->id . '.pdf';
        if (!$artworkPdf->send($filename, true)) {
            throw new Exception('Could not create PDF: ' . $artworkPdf->getError());
        }
    }

    /**
     * @param $id
     * @param $key
     * @return string
     */
    public function actionArtworkApproval($id, $key)
    {
        $model = new JobArtworkApprovalForm();
        $model->job = $this->findJobModel($id, $key);
        if (!$model->job) {
            return $this->redirect(['not-available']);
        }
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->save()) {
                //Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Artwork has been accepted!'));
                return $this->redirect(ReturnUrl::getUrl(['artwork', 'id' => $model->job->id]));
            }
        }
        return $this->render('artwork-approval', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $item_id
     * @param $key
     * @return string
     * @throws HttpException
     */
    public function actionArtworkApprovalItem($id, $item_id, $key)
    {
        $model = new ItemArtworkApprovalForm();
        $model->item = $this->findItemModel($id, $item_id, $key);
        if (!in_array(explode('/', $model->item->status)[1], ['approval'])) {
            throw new HttpException(404, 'The requested item is not in approval status.');
        }
        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->save()) {
                //Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Artwork has been accepted!'));
                return $this->redirect(ReturnUrl::getUrl(['artwork', 'id' => $model->item->product->job->id]));
            }
        }
        return $this->render('artwork-approval-item', ['model' => $model, 'key' => $key]);
    }

    /**
     * @param $id
     * @param $item_id
     * @param $key
     */
    public function actionArtworkDownload($id, $item_id, $key)
    {
        $item = $this->findItemModel($id, $item_id, $key);
        $localFile = $item->artwork->getLocalFile();
        Yii::$app->response->sendFile($localFile);
    }

    /**
     * @param $id
     * @param $key
     * @throws HttpException
     */
    private function validateKey($id, $key)
    {
        if ($key != md5($id . '.' . getenv('APP_COOKIE_VALIDATION_KEY'))) {
            throw new HttpException(405, 'You do not have permission to view the requested page.');
        }
    }

    /**
     * @param $id
     * @param $key
     * @return Job|bool
     * @throws HttpException
     */
    private function findJobModel($id, $key)
    {
        $this->validateKey($id, $key);
        if (($model = Job::findOne($id)) === null) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        if (!in_array($model->status, ['job/draft', 'job/quote', 'job/productionPending', 'job/production'])) {
            return false;
        }
        if ($model->status == 'job/draft' && Yii::$app->user->isGuest) {
            return false;
        }
        return $model;
    }

    /**
     * @param $id
     * @param $key
     * @return Item
     * @throws HttpException
     */
    private function findItemModel($id, $item_id, $key)
    {
        $model = Item::find()->joinWith(['product'])->andWhere(['item.id' => $item_id, 'product.job_id' => $id])->one();
        if ($model === null) {
            throw new HttpException(404, 'The requested page does not exist.');
        }
        return $model;
    }

}
