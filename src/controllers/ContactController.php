<?php

namespace app\controllers;

use app\components\Controller;
use app\components\GearmanManager;
use app\models\Company;
use app\models\Contact;
use app\models\ContactToCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use app\models\Log;
use app\models\search\ContactSearch;
use app\components\ReturnUrl;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\helpers\Html;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "ContactController".
 */
class ContactController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        $searchModel = new ContactSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->merge_id) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Contact has been merged to {merge}.', [
                'merge' => Html::a('contact-' . $model->merge_id, ['//contact/view', 'id' => $model->merge_id]),
            ]));
        } elseif ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Contact is deleted.'));
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionHubSpotPull($id)
    {
        $model = $this->findModel($id);
        if ($model->hubSpotContact) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact was pulled from HubSpot.'));
            HubSpotContact::hubSpotPull($model->hubSpotContact->hub_spot_id);
        } else {
            HubSpotContact::hubSpotPush($model->id);
            Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'Contact was pushed to HubSpot.'));
        }
        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * @inheritdoc
     */
    public function actionPreview($id)
    {
        $model = $this->findModel($id);
        if ($model->merge_id) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Contact has been merged to {merge}.', [
                'merge' => Html::a('contact-' . $model->merge_id, ['//contact/view', 'id' => $model->merge_id]),
            ]));
        } elseif ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Contact is deleted.'));
        }
        return $this->render('preview', ['model' => $model]);
    }

    /**
     * @inheritdoc
     */
    public function actionCreate()
    {
        $model = new Contact;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            GearmanManager::runHubSpotPush(HubSpotContact::className(), $model->id);
            Log::log('created contact', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been created.'));
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
            GearmanManager::runHubSpotPush(HubSpotContact::className(), $model->id);
            Log::log('updated contact', $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new ContactSearch;
        $params = $_GET;
        if (isset($_GET['q'])) {
            $params['ContactSearch']['keywords'] = $_GET['q'];
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;

        /** @var Contact[] $models */
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->getLabelWithEmail()];
        }

        $selected = false;
        if (isset($_GET['ContactSearch']['company_id'])) {
            $company = Company::findOne($_GET['ContactSearch']['company_id']);
            if ($company) {
                $selected = $company->default_contact_id;
            }
        }

        return [
            'results' => $results,
            'selected' => $selected,
        ];
    }

    /**
     * @inheritdoc
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        GearmanManager::runHubSpotPush(HubSpotContact::className(), $model->id);
        Log::log('deleted contact', $model);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been deleted.'));
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            foreach ($model->jobs as $job) {
                $job->contact_id = $model->merge_id;
                $job->save(false);
                GearmanManager::runHubSpotPush(HubSpotDeal::className(), $job->id);
            }
            $model->delete();
            GearmanManager::runHubSpotPush(HubSpotContact::className(), $model->id);
            GearmanManager::runHubSpotPush(HubSpotContact::className(), $model->merge_id);
            Log::log('merged into contact-' . $model->merge_id, $model);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Contact has been merged.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        return $this->render('merge', ['model' => $model]);
    }

    public function actionCompanyAssign($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $company_id = $_POST['Contact']['default_company_id'];
            $contactToCompany = ContactToCompany::find()
                ->notDeleted()
                ->andWhere([
                    'contact_id' => $id,
                    'company_id' => $company_id,
                ])
                ->one();
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->contact_id = $id;
                $contactToCompany->company_id = $company_id;
                if ($contactToCompany->save()) {
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been assigned.'));
                    return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
                }
            }
        } else {
            $model->default_company_id = null;
        }

        return $this->render('company-assign', ['model' => $model]);
    }

    public function actionContactUnassign($id, $company_id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $contactToCompany = ContactToCompany::findOne([
                'contact_id' => $id,
                'company_id' => $company_id,
            ]);
            if ($contactToCompany) {
                if ($model->default_company_id == $contactToCompany->company_id) {
                    Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Default Company cannot be unassigned.'));
                } else {
                    $contactToCompany->delete();
                    Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been unassigned.'));
                }
            }
        }

        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }

    public function actionContactDefault($id, $company_id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $contactToCompany = ContactToCompany::findOne([
                'contact_id' => $id,
                'company_id' => $company_id,
            ]);
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->contact_id = $id;
                $contactToCompany->company_id = $company_id;
                $contactToCompany->save();
            }
            $model->default_company_id = $contactToCompany->company_id;
            if ($model->save(false)) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Company has been set as default.'));
            }
        }

        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }


    /**
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
