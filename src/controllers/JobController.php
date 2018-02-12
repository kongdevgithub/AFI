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
use app\models\Job;
use app\models\Log;
use app\models\Notification;
use app\models\Product;
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
 * This is the class for controller "JobController".
 */
class JobController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new JobSearch;
        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $view = Yii::$app->user->identity->job_view;

        if (Yii::$app->user->can('app_job_quote', ['route' => true])) {
            if ($view == 'quote' || (!$view && in_array($model->status, ['job/draft', 'job/quote', 'job/quoteLost']))) {
                return $this->redirect(['quote', 'id' => $id, 'ru' => ReturnUrl::getRequestToken()]);
            }
        }

        return $this->redirect([$view ? $view : 'production', 'id' => $id, 'ru' => ReturnUrl::getRequestToken()]);
        //return $this->render('view', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);

        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('preview', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionPreviewNotes($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('preview-notes', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionPreviewNotifications($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('preview-notifications', ['model' => $model]);
    }

    /**
     * @param $id
     * @param bool $force
     * @return string
     * @throws HttpException
     */
    public function actionQuote($id, $force = false)
    {
        $model = $this->findModel($id);

        DynamicMenu::add(['label' => $model->getTitle() . ' (quote)', 'url' => Url::current()]);

        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }

        if ($force) {
            $model->resetQuoteGenerated(true, false);
            /** @var BaseJobQuote $jobQuote */
            $jobQuote = new $model->quote_class;
            $jobQuote->saveQuote($model, true);
            die;
        } else {
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
        }

        if ($model->product_imports_pending) {
            $model->spoolProductImport();
            for ($i = 0; $i < 3; $i++) {
                sleep(1);
                $model->refresh();
                if (!$model->product_imports_pending) {
                    break;
                }
            }
        }

        return $this->render('quote', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws HttpException
     */
    public function actionDelivery($id)
    {
        $model = new JobDeliveryForm();
        $model->job = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $model->load($post);
            if ($model->save()) {
                Log::log('updated job delivery', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job delivery has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['delivery', 'id' => $model->job->id]));
            }
        }
        if ($model->job->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }

        return $this->render('delivery', ['model' => $model]);
    }

    /**
     * @param $id
     * @return bool|\yii\web\Response
     */
    public function actionCopy($id)
    {
        $modelCopy = $this->findModel($id);
        $post = Yii::$app->request->post();

        $model = new JobForm();
        $model->scenario = 'copy';
        $model->job = new Job;
        $model->job->scenario = 'create';
        $model->job->copy_job_id = $modelCopy->id;

        if ($post) {
            $model->setAttributes($post);
            $model->job->loadDefaultValues(); // loadDefaultValues after setAttributes
            if ($model->save()) {
                Log::log('created job - copied from job-' . $modelCopy->id, $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been copied.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
        } else {
            $model->job->attributes = $modelCopy->attributes;
            $model->resetJob();
            foreach ($modelCopy->products as $product) {
                $model->products[$product->id] = $product->quantity;
                $model->productsMeta[$product->id] = [
                    'product' => $product,
                    'copy_attachments' => true,
                    'copy_notes' => true,
                ];
                foreach ($product->items as $item) {
                    $model->items[$item->id] = $item->quantity;
                }
            }
        }

        return $this->render('copy', [
            'model' => $model,
            'modelCopy' => $modelCopy,
        ]);
    }

    /**
     * @param $id
     * @return bool|\yii\web\Response
     */
    public function actionVersion($id)
    {
        $modelCopy = $this->findModel($id);
        $post = Yii::$app->request->post();

        $model = new JobForm();
        $model->scenario = 'version';
        $model->job = new Job;
        $model->job->scenario = 'create';
        $model->job->attributes = $modelCopy->attributes;
        $model->resetJob();
        $model->job->fork_version_job_id = $modelCopy->id;

        if (!in_array($model->job->status, ['job/draft', 'job/quote', 'job/productionPending'])) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Version cannot be created unless Job is in status Draft or Quote.'));
        } elseif ($post) {
            $model->setAttributes($post);
            $model->job->loadDefaultValues(); // loadDefaultValues after setAttributes
            if ($model->save()) {
                Log::log('created job - versioned from job-' . $modelCopy->id, $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been versioned.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
        } else {
            foreach ($modelCopy->products as $product) {
                $model->products[$product->id] = $product->quantity;
                foreach ($product->items as $item) {
                    $model->items[$item->id] = $item->quantity;
                }
            }
        }

        return $this->render('version', [
            'model' => $model,
            'modelCopy' => $modelCopy,
        ]);
    }

    /**
     * @param $id
     * @return bool|\yii\web\Response
     */
    public function actionRedo($id)
    {
        $modelCopy = $this->findModel($id);
        $post = Yii::$app->request->post();

        $model = new JobForm();
        $model->scenario = 'redo';
        $model->job = new Job;
        $model->job->scenario = 'create';
        $model->job->redo_job_id = $modelCopy->id;

        if ($post) {
            $model->setAttributes($post);
            $model->job->loadDefaultValues(); // loadDefaultValues after setAttributes
            if ($model->save()) {
                Log::log('created job - redo from job-' . $modelCopy->id, $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been redoered.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
        } else {
            $model->job->attributes = $modelCopy->attributes;
            $model->resetJob();
            foreach ($modelCopy->products as $product) {
                $model->products[$product->id] = $product->quantity;
                foreach ($product->items as $item) {
                    $model->items[$item->id] = $item->quantity;
                }
            }
        }

        return $this->render('redo', [
            'model' => $model,
            'modelCopy' => $modelCopy,
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionReQuote($id)
    {
        $model = $this->findModel($id);
        $model->resetQuoteGenerated();
        Log::log('re-quoted job', $model);
        return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @param $freight_method
     * @return \yii\web\Response
     */
    public function actionRequestFreightQuote($id, $freight_method)
    {
        $model = $this->findModel($id);
        $model->quote_freight_price = null;
        $model->freight_method = $freight_method;
        $model->freight_quote_requested_at = time();
        $model->freight_quote_provided_at = null;
        $model->save(false);
        Log::log('requested freight quote on job', $model);
        return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @param $freight_method
     * @return \yii\web\Response
     */
    public function actionSetFreightQuote($id, $freight_method)
    {
        $model = $this->findModel($id);

        $model->freight_method = $freight_method;

        $boxes = Freight::getBoxes($model);
        $carriers = $model->shippingAddresses && count($model->shippingAddresses) == 1 ? Freight::getCarrierFreight($model->shippingAddresses[0], $boxes) : false;
        if (!isset($carriers[$freight_method])) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Invalid carrier.'));
            return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
        }
        $carrier = $carriers[$freight_method];
        $unboxed = Freight::getUnboxed($model, $boxes);

        $quote = ($unboxed && $carrier['price']) || $carrier['quote'];
        if ($quote) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Despatch is required to quote freight on this job.'));
            return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
        }

        $model->quote_freight_price = $carrier['price'];
        $model->freight_quote_provided_at = time();
        $model->save(false);
        Log::log('set freight quote on job', $model);
        return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionProduction($id)
    {
        $model = $this->findModel($id);
        DynamicMenu::add(['label' => $model->getTitle() . ' (production)', 'url' => Url::current()]);

        if ($model->quote_generated != 1) {
            $model->spoolQuote();
        }
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('production', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionDespatch($id)
    {
        $model = $this->findModel($id);
        DynamicMenu::add(['label' => $model->getTitle() . ' (despatch)', 'url' => Url::current()]);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }

        $packageItemForm = new PackageItemForm();
        $packageItemForm->job = $model;
        $post = Yii::$app->request->post();
        if ($packageItemForm->load($post) && $packageItemForm->save()) {
            return $this->redirect(ReturnUrl::getUrl(['despatch', 'id' => $model->id]));
        }

        return $this->render('despatch', [
            'model' => $model,
            'packageItemForm' => $packageItemForm,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionFinance($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'finance';
        $post = Yii::$app->request->post();

        DynamicMenu::add(['label' => $model->getTitle() . ' (finance)', 'url' => Url::current()]);

        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }

        if ($post) {
            if ($model->load($post) && $model->save()) {
                Log::log('updated job', $model);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['finance', 'id' => $model->id]));
            }
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('finance', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionSort($id)
    {
        $model = $this->findModel($id);
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('sort', ['model' => $model]);
    }

    /**
     * @return string|\yii\web\Response
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
     */
    public function actionUpdate($id)
    {
        $model = new JobForm();
        $model->scenario = 'update';
        $model->job = $this->findModel($id);
        $model->job->scenario = 'update';

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
     * @throws \yii\db\Exception
     * @throws \yii\web\HttpException
     */
    public function actionDiscount($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'discount';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $transaction = Yii::$app->dbData->beginTransaction();
            if ($model->apply_discount_to_products) {
                $model->quote_discount_price = $model->quote_discount_price / $model->quote_markup;
                $productDiscounts = 0;
                $rateCost = 0;
                $ratePrice = 0;
                foreach ($model->products as $product) {
                    $productDiscounts += $product->quote_discount_price;
                    if ($product->quote_class == RateProductQuote::className()) {
                        $rateCost += $product->quote_total_cost;
                        $ratePrice += $product->quote_factor_price;
                    }
                }
                $totalMargin = ($model->quote_wholesale_price - $ratePrice) - ($model->quote_total_cost - $rateCost) + $productDiscounts;
                $productMargins = [];
                foreach ($model->products as $product) {
                    if ($product->quote_class == RateProductQuote::className()) continue;
                    $productMargin = $product->quote_factor_price - $product->quote_total_cost;
                    $productMargins[$product->id] = $productMargin ? $totalMargin / $productMargin : 0;
                }

                foreach ($model->products as $product) {
                    if ($product->quote_class == RateProductQuote::className()) {
                        $product->quote_discount_price = 0;
                    } else {
                        $product->quote_discount_price = $productMargins[$product->id] > 0 ? $model->quote_discount_price / $productMargins[$product->id] : 0;
                    }
                    $product->save(false);
                }
                $model->quote_discount_price = 0;
                $model->save(false);
                $transaction->commit();
                $model->resetQuoteGenerated(false);
                Log::log('updated discount (apply to products)', $model);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
            } else {
                if ($model->save()) {
                    foreach ($model->products as $product) {
                        $product->quote_discount_price = 0;
                        $product->save(false);
                    }
                    $transaction->commit();
                    $model->resetQuoteGenerated(false);
                    Log::log('updated discount (apply to job)', $model);
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
                    return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
                }
            }
            $transaction->rollBack();
        }
        if (!Yii::$app->request->isPost) {
            $model->apply_discount_to_products = 1;
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('discount', ['model' => $model]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionDiscountRemove($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'discount';
        $model->quote_discount_price = 0;
        $model->save();
        $model->resetQuoteGenerated(false);
        Log::log('remove discount', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Discount has been removed.'));
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionSurcharge($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'surcharge';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->resetQuoteGenerated(false);
            Log::log('updated surcharge', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('surcharge', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionFreight($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'freight';
        $model->freight_quote_provided_at = time();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->resetQuoteGenerated(false);
            Log::log('updated freight', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->setAttributes(Yii::$app->request->get());
        }

        return $this->render('freight', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionBoxes($id)
    {
        $model = $this->findModel($id);
        return $this->render('boxes', ['model' => $model]);
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

        if (!in_array($model->status, ['job/draft', 'job/quote', 'job/productionPending']) && !Yii::$app->user->can('_update_job_due')) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Only managers can change due dates after production.'));
        } elseif ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->resetQuoteGenerated(false);
            $log = Log::log('updated due', $model);
            $changes = Html::ul($log->getAuditTrails(), ['encode' => false]);
            $to = $model->getChangedAlertEmails();
            if ($to) {
                EmailManager::sendJobChangedAlert($to, $model, $log);
                Notification::add('danger', 'Job Dates Changed', $changes, $model);
            }
            if ($model->correction_reason) {
                $action = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;
                Correction::add($action, $model->correction_reason, $changes, $model);
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
     * @return string
     */
    public function actionQuotePdfAttach($id)
    {
        $model = $this->findModel($id);
        $model->attachQuote();
        Log::log('attached new quote pdf', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'A new Quote PDF has been attached to the Job.'));
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionQuoteEmail($id)
    {
        $model = new JobQuoteEmailForm();
        $model->job = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->send()) {
                Log::log('emailed quote', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Quote has been emailed.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
            Yii::$app->getSession()->addFlash('error', Yii::t('app', 'Could not email quote.'));
        }
        return $this->render('quote-email', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionQuoteEmailPreview($id)
    {
        $model = $this->findModel($id);

        $this->layout = false;
        $details = $this->renderPartial('_quote-email-details', ['model' => $model]);
        $email = $this->renderPartial('../../mail/layouts/html', [
            'content' => $this->renderPartial('../../mail/quote-approval/html', [
                'job' => $model,
            ]),
        ]);
        return $details . $email;
    }

    /**
     * @param $id
     * @param bool $html
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionInvoicePdf($id, $html = false)
    {
        $model = $this->findModel($id);
        if (!$html) {
            $invoicePdf = PdfManager::getJobInvoice($model);
            $filename = 'invoice_' . Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->vid . '_pi.pdf';
            if (!$invoicePdf->send($filename, true)) {
                throw new Exception('Could not create PDF: ' . $invoicePdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('invoice-pdf', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionInvoiceEmail($id)
    {
        $model = new JobInvoiceEmailForm();
        $model->job = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->send()) {
                Log::log('emailed invoice', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Invoice has been emailed.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
            Yii::$app->getSession()->addFlash('error', Yii::t('app', 'Could not email invoice.'));
        }
        return $this->render('invoice-email', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionInvoiceEmailPreview($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        $details = $this->renderPartial('_invoice-email-details', ['model' => $model]);
        $email = $this->renderPartial('../../mail/layouts/html', [
            'content' => $this->renderPartial('../../mail/job-invoice/html', [
                'job' => $model,
            ]),
        ]);
        return $details . $email;
    }

    /**
     * @param $id
     * @param bool $html
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionArtworkPdf($id, $html = false)
    {
        $model = $this->findModel($id);
        if (!$html) {
            $artworkPdf = PdfManager::getJobArtwork($model);
            $filename = 'artwork_' . Inflector::slug($model->company->name) . '_' . Inflector::slug($model->name) . '_' . $model->vid . '.pdf';
            if (!$artworkPdf->send($filename, true)) {
                throw new Exception('Could not create PDF: ' . $artworkPdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('artwork-pdf', ['model' => $model]);
    }


    /**
     * @param $id
     * @return string
     * @throws Exception
     * @throws \yii\web\HttpException
     */
    public function actionArtworkEmail($id)
    {
        $model = new JobArtworkEmailForm();
        $model->job = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            if ($model->send()) {
                Log::log('emailed artwork', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Artwork has been emailed.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->job->id]));
            }
            Yii::$app->getSession()->addFlash('error', Yii::t('app', 'Could not email artwork.'));
        }
        return $this->render('artwork-email', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionArtworkEmailPreview($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        $details = $this->renderPartial('_artwork-email-details', ['model' => $model]);
        $email = $this->renderPartial('../../mail/layouts/html', [
            'content' => $this->renderPartial('../../mail/artwork-approval/html', [
                'job' => $model,
            ]),
        ]);
        return $details . $email;
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionBillingAddress($id)
    {
        $model = $this->findModel($id);

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
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        Yii::$app->getSession()->addFlash('error', Yii::t('app', 'Delete is disabled. Please set to Cancelled or Quote Lost.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
        //$model = $this->findModel($id);
        //if (!Yii::$app->user->can('manager') && $model->status != 'job/draft') {
        //    Yii::$app->getSession()->addFlash('error', Yii::t('app', 'Job cannot be deleted unless status is Draft - contact a manager.'));
        //    return $this->redirect(ReturnUrl::getUrl(['index']));
        //}
        //$model->delete();
        //GearmanManager::runHubSpotPush(HubSpotDeal::className(), $model->id);
        //Log::log('deleted job', $model);
        //Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been deleted.'));
        //return $this->redirect(ReturnUrl::getUrl(['index']));
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
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionStatusHistory($id)
    {
        $model = $this->findModel($id);
        return $this->render('status-history', ['model' => $model]);
    }

    /**
     * @param $id
     * @param string|null $item_types
     * @param bool $html
     * @return string
     * @throws Exception
     */
    public function actionProductionPdf($id, $item_types = null, $html = false)
    {
        $model = $this->findModel($id);
        if (!$html) {
            $pdf = PdfManager::getJobProduction($model, $item_types);
            $filename = $model->vid . '-production' . ($item_types ? '-' . Inflector::slug($item_types) : '') . '.pdf';
            if (!$pdf->send($filename, true)) {
                throw new Exception('Could not create PDF: ' . $pdf->getError());
            }
            return '';
        }
        $this->layout = false;
        return $this->render('production-pdf', ['model' => $model, 'item_types' => $item_types]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionPrint($id)
    {
        $model = new JobPrintForm();
        $model->job = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Log::log('printed', $model->job);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job has been printed.'));
            }
            return $this->redirect(ReturnUrl::getUrl(['/job/view', 'id' => $model->job->id]));
        } else {
            $model->setAttributes(Yii::$app->request->get());
        }

        if ($model->job->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Job is deleted.'));
        }
        return $this->render('print', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionShippingAddressImport($id)
    {
        $model = new ShippingAddressImportForm();
        $model->model = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {
            Log::log('uploaded addresses', $model->model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Addresses have been uploaded.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->model->id]));
        }

        return $this->render('shipping-address-import', ['model' => $model]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionAddressPackageCreate($id)
    {
        $post = Yii::$app->request->post();

        $formModel = new AddressPackageCreateForm();
        $model = $this->findModel($id);

        if ($post) {
            $formModel->ids = $post['ids'];
            if ($formModel->load($post) && $formModel->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been created.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('address-package-create', [
            'model' => $model,
            'formModel' => $formModel,
        ]);
    }

    /**
     * @param $id
     * @param bool $force
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionDearPush($id, $force = false)
    {
        $model = $this->findModel($id);
        GearmanManager::runDearPush(DearSale::className(), $id, $force);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job will be pushed to Dear in the background.'));
        //if (DearSale::dearPush($id, $force)) {
        //} else {
        //    Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'Something went wrong when pushing Job to Dear:<br>{errors}', [
        //        'errors' => implode(', ', DearSale::$pushErrors),
        //    ]));
        //}
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
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
            $export->model_name = JobSearch::className();
            $export->model_params = Json::encode(Yii::$app->request->post('JobSearch'));
            if ($export->save()) {
                $export->spoolGearman();
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job export has begun.'));
                return $this->redirect(['/export/view', 'id' => $export->id, 'ru' => ReturnUrl::getRequestToken()]);
            }
        }
        return $this->render('export', [
            'searchParams' => (array)Yii::$app->request->get('JobSearch'),
        ]);
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


    /**
     * @param $id
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionPrice($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $transaction = Yii::$app->dbData->beginTransaction();
            foreach ($post['Product'] as $product_id => $attributes) {
                $product = Product::findOne($product_id);

                $factor = $attributes['price'] ? $attributes['price'] / $model->quote_markup / $product->quote_total_price : '0.0000001';
                if (!Yii::$app->user->can('_reduce_product_factor')) {
                    if ($factor < 1) $factor = 1;
                }

                $product->quote_class = BaseProductQuote::className();
                $product->quote_factor = $factor;
                $product->preserve_unit_prices = 0;
                $product->prevent_rate_prices = 1;

                if (!$product->save()) {
                    $transaction->rollBack();
                    Yii::$app->getSession()->addFlash('danger', 'product-' . $product->id . ' could not be saved: ' . Helper::getErrorString($product));
                }
            }
            $model->resetQuoteGenerated();
            $transaction->commit();

            Log::log('updated job prices', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job prices have been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['price', 'id' => $model->id]));
        }

        return $this->render('price', ['model' => $model]);
    }

    /**
     * Saves the search criteria to be reused.
     * @param null $delete
     * @return mixed
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
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job search has been deleted.'));
            }
            return $this->redirect(ReturnUrl::getUrl(['index']));
        }
        $search = new Search();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $search->name = $post['Search']['name'];
            $search->user_id = Yii::$app->user->id;
            $search->model_name = JobSearch::className();
            $search->model_params = Json::encode($post['JobSearch']);
            if ($search->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Job search has been saved.'));
                return $this->redirect(ReturnUrl::getUrl(['index', 'JobSearch' => Json::decode($search->model_params)]));
            }
        } else {
            $search->model_params = Json::encode(Yii::$app->request->get('JobSearch'));
        }
        return $this->render('save-search', [
            'search' => $search,
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws Exception
     * @throws HttpException
     */
    public function actionBulkPackage($id)
    {
        $model = new ItemBulkPackageForm();
        $model->job = $this->findModel($id);

        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Items have been bulk completed.'));
                return $this->redirect(ReturnUrl::getUrl(['despatch', 'id' => $model->job->id]));
            }
        } else {
            $ids = [];
            foreach ($model->job->products as $product) {
                foreach ($product->items as $item) {
                    foreach ($item->units as $unit) {
                        if (!$unit->package_id) {
                            $ids[$item->id] = 'item-' . $item->id;
                            break;
                        }
                    }
                }
            }
            $model->package_id = 'package-new';
            $model->ids = implode("\n", $ids);
        }

        // render the page
        return $this->render('bulk-package', array(
            'model' => $model,
        ));
    }

    /**
     * @param $id
     * @param string $switch
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionPreserveUnitPrices($id, $switch = 'on')
    {
        $model = $this->findModel($id);
        foreach ($model->products as $product) {
            $product->preserve_unit_prices = ($switch == 'on' ? 1 : 0);
            $product->save(false);
        }
        return $this->redirect(ReturnUrl::getUrl(['quote', 'id' => $model->id]));
    }

}
