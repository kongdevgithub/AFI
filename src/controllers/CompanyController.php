<?php

namespace app\controllers;

use app\components\Controller;
use app\components\GearmanManager;
use app\components\YdCsv;
use app\models\Address;
use app\models\Company;
use app\models\ContactToCompany;
use app\models\form\CompanyForm;
use app\models\form\CompanyRateImportForm;
use app\models\form\ShippingAddressImportForm;
use app\models\HubSpotCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use app\models\Log;
use app\models\search\CompanyRateSearch;
use app\models\search\CompanySearch;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "CompanyController".
 */
class CompanyController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }


    /**
     * @param $id
     * @return Response
     */
    public function actionFixAddresses($id)
    {
        $model = $this->findModel($id);
        $hasBilling = false;
        foreach ($model->addresses as $address) {
            if ($hasBilling) {
                $address->type = Address::TYPE_SHIPPING;
                $address->save(false);
            }
            if ($address->type == Address::TYPE_BILLING) {
                $hasBilling = true;
            }
        }
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @inheritdoc
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->merge_id) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Company has been merged to {merge}.', [
                'merge' => Html::a('company-' . $model->merge_id, ['//company/view', 'id' => $model->merge_id]),
            ]));
        } elseif ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Company is deleted.'));
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionHubSpotPull($id)
    {
        $model = $this->findModel($id);
        if ($model->hubSpotCompany) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company was pulled from HubSpot.'));
            HubSpotCompany::hubSpotPull($model->hubSpotCompany->hub_spot_id);
        } else {
            HubSpotCompany::hubSpotPush($model->id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company was pushed to HubSpot.'));
        }
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @inheritdoc
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);
        if ($model->merge_id) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Company has been merged to {merge}.', [
                'merge' => Html::a('company-' . $model->merge_id, ['//company/view', 'id' => $model->merge_id]),
            ]));
        } elseif ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Company is deleted.'));
        }
        return $this->render('preview', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new CompanyForm();
        $model->company = new Company;
        $model->company->scenario = 'create';
        $model->company->loadDefaultValues();

        $model->setAttributes(Yii::$app->request->post());
        if (Yii::$app->request->post() && $model->save()) {
            Log::log('created company', $model->company);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been created.'));
            return $this->redirect(['view', 'id' => $model->company->id]);
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
            $model->company->staff_rep_id = Yii::$app->user->id;
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionUpdate($id)
    {
        $model = new CompanyForm();
        $model->company = $this->findModel($id);
        $model->company->scenario = 'update';

        $model->setAttributes(Yii::$app->request->post());
        if (Yii::$app->request->post() && $model->save()) {
            Log::log('updated company', $model->company);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->company->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        return $this->render('update', ['model' => $model]);
    }


    public function actionRates($id)
    {
        $model = $this->findModel($id);

        $searchModel = new CompanyRateSearch;
        $params = Yii::$app->request->get();
        $params['CompanyRateSearch']['company_id'] = $model->id;
        $dataProvider = $searchModel->search($params);

        return $this->render('rates', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Rate Importer and Updater
     *
     * @return mixed
     */
    public function actionRateImport($id)
    {
        $model = new CompanyRateImportForm();
        $model->company = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company Rates have been uploaded.'));
            return $this->redirect(ReturnUrl::getUrl(['company/rates', 'id' => $model->company->id]));
        }

        return $this->render('rate-import', [
            'model' => $model,
        ]);
    }


    /**
     * Rate Exporter
     *
     * @param $id
     * @return mixed
     */
    public function actionRateExport($id)
    {
        $model = $this->findModel($id);
        $csv = [];
        foreach ($model->companyRates as $companyRate) {
            $csv[] = [
                'product_type' => $companyRate->productType->getBreadcrumbString(' > '),
                'item_type' => $companyRate->itemType->name,
                'option' => $companyRate->option->name,
                'component' => $companyRate->component->code,
                'size' => $companyRate->size,
                'price' => $companyRate->price,
                'options' => $companyRate->getCompanyRateOptionsString(),
            ];
        }
        return Yii::$app->response->sendContentAsFile(YdCsv::arrayToCsv($csv), 'rate-export-' . $model->id . '.csv');
    }

    /**
     * @inheritdoc
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'status';

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction->commit();
            Log::log('updated status', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new CompanySearch;
        $params = $_GET;
        if (isset($_GET['q'])) {
            $params['CompanySearch']['keywords'] = $_GET['q'];
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->name];
        }
        return ['results' => $results];
    }

    /**
     * @param $id
     * @return array|Company
     * @throws \yii\web\HttpException
     */
    public function actionJsonView($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $this->findModel($id);
    }

    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        GearmanManager::runHubSpotPush(HubSpotCompany::className(), $model->id);
        Log::log('deleted company', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been deleted.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
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
    public function actionMerge($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'merge';

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            foreach ($model->jobs as $job) {
                $job->company_id = $model->merge_id;
                $job->save(false);
                GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
            }
            foreach ($model->contacts as $contact) {
                $contact->default_company_id = $model->merge_id;
                $contact->save(false);
                GearmanManager::runHubSpotPush(HubSpotContact::className(), $contact->id);
            }
            foreach ($model->addresses as $address) {
                $address->model_id = $model->merge_id;
                $address->type = Address::TYPE_SHIPPING;
                $address->save(false);
            }
            foreach ($model->notes as $note) {
                $note->model_id = $model->merge_id;
                $note->save(false);
            }
            $model->delete();
            $transaction->commit();
            GearmanManager::runHubSpotPush(HubSpotCompany::className(), $model->id);
            GearmanManager::runHubSpotPush(HubSpotCompany::className(), $model->merge_id);
            Log::log('merged into company-' . $model->merge_id, $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been merged.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('merge', ['model' => $model]);
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
                Log::log('updated shipping address', $model);
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Shipping Address has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
            }
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
        }
        Log::log('deleted shipping address', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Shipping Address has been deleted.'));
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * @param $id
     * @return string|Response
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
     * @return string|Response
     */
    public function actionContactAssign($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $contact_id = $_POST['Company']['default_contact_id'];
            $contactToCompany = ContactToCompany::find()
                ->notDeleted()
                ->andWhere([
                    'company_id' => $id,
                    'contact_id' => $contact_id,
                ])
                ->one();
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->company_id = $id;
                $contactToCompany->contact_id = $contact_id;
                if ($contactToCompany->save()) {
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been assigned.'));
                    return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
                }
            }
        } else {
            $model->default_contact_id = null;
        }

        return $this->render('contact-assign', ['model' => $model]);
    }

    /**
     * @param $id
     * @param $contact_id
     * @return Response
     */
    public function actionContactUnassign($id, $contact_id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $contactToCompany = ContactToCompany::findOne([
                'company_id' => $id,
                'contact_id' => $contact_id,
            ]);
            if ($contactToCompany) {
                if ($model->default_contact_id == $contactToCompany->contact_id) {
                    Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Default Contact cannot be unassigned.'));
                } else {
                    $contactToCompany->delete();
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been unassigned.'));
                }
            }
        }

        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * @param $id
     * @param $contact_id
     * @return Response
     */
    public function actionContactDefault($id, $contact_id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $contactToCompany = ContactToCompany::findOne([
                'company_id' => $id,
                'contact_id' => $contact_id,
            ]);
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->company_id = $id;
                $contactToCompany->contact_id = $contact_id;
                $contactToCompany->save();
            }
            $model->default_contact_id = $contactToCompany->contact_id;
            if ($model->save(false)) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been set as default.'));
            }
        }

        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
