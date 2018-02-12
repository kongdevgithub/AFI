<?php

namespace app\controllers;

use app\components\Controller;
use app\components\PdfManager;
use app\models\form\AddressPackageCreateForm;
use app\models\form\PackageAddressForm;
use app\models\form\PackageDimensionsForm;
use app\models\form\PackageForm;
use app\models\form\PackagePickupForm;
use app\models\form\PackagePrintForm;
use app\models\form\PackageSystemPrintForm;
use app\models\Package;
use app\components\ReturnUrl;
use app\models\search\PackageSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\base\Exception;
use yii\web\HttpException;

/**
 * This is the class for controller "app\controllers\PackageController".
 */
class PackageController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Package models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PackageSearch;
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
        if ($model->deleted_at) {
            Yii::$app->getSession()->addFlash('danger', Yii::t('app', 'Package is deleted.'));
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new Package model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Package;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }


    /**
     * Updates an existing Package model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = new PackageForm();
        $model->package = $this->findModel($id);
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package has been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->package->id]));
            }
        }

        return $this->render('update', compact('model'));
    }

    /**
     * Creates an new overflow package from and existing Package model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionOverflow($id)
    {
        $modelCopy = $this->findModel($id);
        $model = new PackageForm();
        $model->scenario = 'overflow';
        $model->package = new Package();
        $model->package->loadDefaultValues();
        $model->package->scenario = 'create';
        $model->package->overflow_package_id = $modelCopy->id;
        $post = Yii::$app->request->post();

        if ($post) {
            $model->setAttributes($post);
            if ($model->save()) {
                foreach ($modelCopy->units as $unit) {
                    $unit->clearCache();
                }
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package has been created.'));
                return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->package->id]));
            }
        } else {
            $model->quantity = 1;
            foreach (['pickup_id', 'package_type_id', 'type', 'cartons', 'width', 'length', 'height', 'dead_weight'] as $attribute) {
                $model->package->$attribute = $modelCopy->$attribute;
            }
            foreach (['name', 'street', 'postcode', 'city', 'state', 'country', 'contact', 'phone', 'instructions'] as $attribute) {
                $model->address->$attribute = $modelCopy->address->$attribute;
            }
        }

        return $this->render('overflow', compact('model'));
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
            $pdf = PdfManager::getPackage($model);
            $filename = 'package-' . $model->id . '.pdf';
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
     * @param int $id
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\HttpException
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'status';

        $transaction = Yii::$app->dbData->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $transaction->commit();
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        $transaction->rollBack();

        return $this->render('status', ['model' => $model]);
    }

    /**
     * @param bool $confirm
     * @return string
     */
    public function actionAddress($confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new PackageAddressForm();
        $model->ids = $post['ids'];

        if ($confirm && $post) {
            $model->setAttributes($post);
            if ($model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('address', [
            'model' => $model,
        ]);
    }

    /**
     * @param bool $confirm
     * @return string
     */
    public function actionDimensions($confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new PackageDimensionsForm();
        $model->ids = $post['ids'];

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('dimensions', [
            'model' => $model,
        ]);
    }

    /**
     * @param bool $confirm
     * @return string
     */
    public function actionPickup($confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new PackagePickupForm();
        $model->ids = isset($post['ids']) ? $post['ids'] : [];

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been updated.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('pickup', [
            'model' => $model,
        ]);
    }

    /**
     * @param null $id
     * @param bool $confirm
     * @return string
     */
    public function actionPrint($id = null, $confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new PackagePrintForm();
        $model->ids = !empty($post['ids']) ? $post['ids'] : [];
        if ($id) {
            $model->ids[] = $id;
        }

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been printed.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('print', [
            'model' => $model,
        ]);
    }

    /**
     * @param bool $confirm
     * @return string
     */
    public function actionAddressCreate($confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new AddressPackageCreateForm();
        $model->ids = $post['ids'];

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Packages have been created.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('address-create', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionSystemPrint()
    {
        $model = new PackageSystemPrintForm;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Tests have been printed.'));
            return $this->redirect(ReturnUrl::getUrl(['/print-spool/test']));
        }
        return $this->render('test', [
            'model' => $model,
        ]);
    }


    /**
     * Deletes an existing Package model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Package has been deleted.'));

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
     * Finds the Package model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Package the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Package::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
