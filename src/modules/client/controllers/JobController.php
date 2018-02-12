<?php

namespace app\modules\client\controllers;

use app\components\DynamicMenu;
use app\components\EmailManager;
use app\components\GearmanManager;
use app\components\PdfManager;
use app\models\Address;
use app\models\form\JobForm;
use app\models\form\JobQuoteEmailForm;
use app\models\HubSpotDeal;
use app\models\Job;
use app\models\Log;
use app\models\Notification;
use app\models\search\JobSearch;
use app\components\ReturnUrl;
use Yii;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * This is the class for controller "JobController".
 */
class JobController extends Controller
{
    /**
     * @var string
     */
    public $layout = '@app/views/layouts/main';

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new JobSearch;
        $params = Yii::$app->request->get();

        // client permissions
        $params['JobSearch']['client_company_id'] = Yii::$app->user->identity->getClientCompanies();

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('preview', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        DynamicMenu::add(['label' => $model->getTitle() . ' (quote)', 'url' => Url::current()]);

        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }

        if ($model->quote_generated != 1) {
            $model->spoolQuote();
            for ($i = 0; $i < 3; $i++) {
                sleep(1);
                $model->refresh();
                if ($model->quote_generated == 1) {
                    break;
                }
            }
        }

        return $this->render('view', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionSort($id)
    {
        $model = $this->findModel($id);
        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('sort', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new JobForm();
        $model->scenario = 'create';
        $model->job = new Job;
        $model->job->scenario = 'create';

        if (Yii::$app->request->post()) {
            $model->setAttributes(Yii::$app->request->post());
        }
        $model->job->loadDefaultValues(); // loadDefaultValues after setAttributes
        $model->job->quote_win_chance = 50;

        if (Yii::$app->request->post()) {
            if ($model->save()) {
                Log::log('created job', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been created.'));
                return $this->redirect(['view', 'id' => $model->job->id]);
            }
        } else {
            $model->setAttributes(Yii::$app->request->get());
            //$model->job->staff_lead_id = Yii::$app->user->id;
            $model->job->staff_rep_id = Yii::$app->user->id;
            //$model->job->staff_csr_id = Yii::$app->user->id;
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws HttpException
     */
    public function actionUpdate($id)
    {
        $model = new JobForm();
        $model->scenario = 'update';
        $model->job = $this->findModel($id);
        $model->job->scenario = 'update';
        $model->job->loadDefaultValues();

        // client permissions
        if (!$model->job->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if (Yii::$app->request->post()) {
            $model->setAttributes(Yii::$app->request->post());
            if ($model->save()) {
                Log::log('updated job', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
        } else {
            foreach ($model->job->products as $product) {
                $model->products[$product->id] = $product->quantity;
                $model->preserve_unit_prices[$product->id] = $product->preserve_unit_prices;
                foreach ($product->items as $item) {
                    $model->items[$item->id] = $item->quantity;
                }
            }
            $model->setAttributes(Yii::$app->request->get());
        }
        return $this->render('update', ['model' => $model]);
    }


    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionDue($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'due';

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if (!in_array($model->status, ['job/draft', 'job/quote', 'job/productionPending']) && !Yii::$app->user->can('_update_job_due')) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Only managers can change due dates after production.'));
        } elseif ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->resetQuoteGenerated(false);
            $log = Log::log('updated due', $model);
            $to = $model->getChangedAlertEmails();
            if ($to) {
                EmailManager::sendJobChangedAlert($to, $model, $log);
                Notification::add('danger', 'Job Dates Changed', Html::ul($log->getAuditTrails(), ['encode' => false]), $model);
            }
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('due', ['model' => $model]);
    }

    /**
     * @param $id
     * @param null $fork
     * @return string|\yii\web\Response
     * @throws HttpException
     */
    public function actionStatus($id, $fork = null)
    {
        $model = $this->findModel($id);
        $model->scenario = 'status';
        $post = Yii::$app->request->post();

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        // fork message
        if ($fork) {
            Yii::$app->getSession()->addFlash('info', Yii::t('app', 'Please choose the status of the existing job before proceeding.'));
        }

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load($post) && $model->save()) {
            $transaction->commit();
            GearmanManager::runHubSpotPush(HubSpotDeal::className(), $model->id);
            Log::log('updated status', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @param $id
     * @param bool $html
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionQuotePdf($id, $html = false)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if (!$html) {
            $quotePdf = PdfManager::getJobQuote($model);
            $filename = Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->vid . '.pdf';
            if (!$quotePdf->send($filename, true)) {
                throw new Exception('Could not create PDF: ' . $quotePdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('quote-pdf', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionBillingAddress($id)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        if ($model->billingAddress->load(Yii::$app->request->post()) && $model->billingAddress->save()) {
            foreach ($model->addresses as $address) {
                if ($address->type == Address::TYPE_BILLING && $address->id != $model->billingAddress->id) {
                    $address->delete();
                }
            }
            Log::log('updated billing address', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Billing Address has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        }
        return $this->render('billing-address', ['model' => $model]);
    }

    /**
     * @param $id
     * @param null $address_id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionShippingAddress($id, $address_id = null)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $address = false;
        if ($address_id) {
            foreach ($model->addresses as $_address) {
                if ($address_id == $_address->id && $_address->type == Address::TYPE_SHIPPING) {
                    $address = $_address;
                    break;
                }
            }
        } else {
            $address = new Address();
            $address->model_id = $model->id;
            $address->model_name = $model->className();
            $address->type = Address::TYPE_SHIPPING;
        }

        $post = Yii::$app->request->post();
        if ($post) {
            if ($address->load($post) && $address->save()) {
                $model->updateFreightDays();
                Log::log('updated shipping address', $model);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Shipping Address has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
            }
            //} else {
            //$address->contact = $model->contact->label;
            //$address->phone = $model->contact->phone;
        }
        return $this->render('shipping-address', ['model' => $model, 'address' => $address]);
    }

    /**
     * @param $id
     * @param $address_id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionShippingAddressDelete($id, $address_id)
    {
        $model = $this->findModel($id);

        // client permissions
        if (!$model->checkAccess()) {
            throw new HttpException(403, Yii::t('app', 'You are not allowed to perform this action.'));
        }

        $address = false;
        foreach ($model->addresses as $_address) {
            if ($address_id == $_address->id && $_address->type == Address::TYPE_SHIPPING) {
                $address = $_address;
                break;
            }
        }
        if ($address) {
            $address->delete();
            $model->updateFreightDays();
        }
        Log::log('deleted shipping address', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Shipping Address has been deleted.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Job model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Job the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Job::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

}
