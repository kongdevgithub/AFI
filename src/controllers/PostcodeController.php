<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\Address;
use app\models\Postcode;
use app\models\search\PostcodeSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use kartik\form\ActiveForm;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * This is the class for controller "app\controllers\PostcodeController".
 */
class PostcodeController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Postcode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostcodeSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Postcode model.
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
     * Creates a new Postcode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Postcode;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Postcode has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Postcode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Postcode has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing Postcode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Postcode has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Postcode model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Postcode the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Postcode::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }

    /**
     * @param string $key
     * @param bool $label
     * @param null $formType
     * @return array
     */
    public function actionAjaxLookup($key = null, $label = null, $formType = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (in_array($key, ['address-postcode', 'packageaddressform-postcode'])) $key = null;

        if (!isset($_POST['postcode'])) {
            return null;
        }
        $model = new Address();
        $response = [];
        $data = [
            'city' => [],
            'state' => [],
            'country' => [],
        ];
        $postcodes = [];
        if (!empty($_POST['postcode'])) {
            $postcodes = Postcode::find()->andWhere(['postcode' => $_POST['postcode']])->all();
        }
        //if (empty($postcodes)) {
        //    $postcode = Postcode::find()->andWhere(['city' => $_POST['city']])->one();
        //    if ($postcode) {
        //        $postcodes = Postcode::find()->andWhere(['postcode' => $postcode->postcode])->all();
        //    }
        //}
        if (isset($postcodes)) {
            foreach ($postcodes as $postcode) {
                $data['city'][$postcode->city] = $postcode->city;
                $data['state'][$postcode->state] = $postcode->state;
                $data['country'][$postcode->country] = $postcode->country;
                if (!isset($response['postcode'])) {
                    $response['postcode'] = $postcode->postcode;
                }
            }
        }

        /** @var ActiveForm $form */
        ob_start();
        $form = ActiveForm::begin([
            'formConfig' => ['labelSpan' => 0],
            'type' => $formType,
        ]);
        ob_end_clean();

        foreach ($data as $field => $values) {
            $options = $key ? [
                'id' => "Addresses_{$key}_{$field}",
                'name' => "Addresses[$key][{$field}]",
                'class' => "form-control address-{$field}",
            ] : [
                'class' => "form-control address-{$field}",
            ];
            if ($values) {
                asort($values);
                if (count($values) > 1) {
                    $options['prompt'] = '';
                } else {
                    $model->$field = current($values);
                }
                $response[$field] = $form->field($model, $field)
                    ->dropDownList($values, $options)
                    ->label($label ? null : false)
                    ->render();
            } else {
                $options['maxlength'] = true;
                $response[$field] = $form->field($model, $field)
                    ->textInput($options)
                    ->label($label ? null : false)
                    ->render();
            }
        }

        return $response;
    }

}
