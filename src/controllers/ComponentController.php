<?php

namespace app\controllers;

use app\components\Controller;
use app\components\GearmanManager;
use app\models\Component;
use app\models\DearProduct;
use app\models\Export;
use app\models\form\ComponentPrintForm;
use app\models\search\ComponentSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;
use yii\helpers\Json;
use yii\web\HttpException;
use dmstr\bootstrap\Tabs;
use app\components\ReturnUrl;
use yii\web\Response;

/**
 * This is the class for controller "app\controllers\ComponentController".
 */
class ComponentController extends Controller
{
    use AccessBehaviorTrait;
    use TwoFactorTrait;

    /**
     * Lists all Component models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ComponentSearch;
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
            $export->model_name = ComponentSearch::className();
            $export->model_params = Json::encode(Yii::$app->request->post('ComponentSearch'));
            if ($export->save()) {
                $export->spoolGearman();
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component export has begun.'));
                return $this->redirect(['/export/view', 'id' => $export->id, 'ru' => ReturnUrl::getRequestToken()]);
            }
        }
        return $this->render('export', [
            'searchParams' => (array)Yii::$app->request->get('ComponentSearch'),
        ]);
    }

    /**
     * Displays a single Component model.
     * @param integer $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        Tabs::rememberActiveState();
        $model = $this->findModel($id);

        return $this->render('view', compact('model'));
    }

    /**
     * Creates a new Component model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Component;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            GearmanManager::runDearPush(DearProduct::className(), $model->id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    /**
     * Updates an existing Component model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            GearmanManager::runDearPush(DearProduct::className(), $model->id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!\Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }


    /**
     * Deletes an existing Component model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        GearmanManager::runDearPush(DearProduct::className(), $model->id);
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * @return array
     */
    public function actionJsonList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $searchModel = new ComponentSearch;
        $params = $_GET;
        if (isset($_GET['q'])) {
            $params['ComponentSearch']['keywords'] = $_GET['q'];
        }

        $dataProvider = $searchModel->search($params);
        $dataProvider->pagination->pageSize = 200;
        $models = $dataProvider->getModels();
        $results = [['id' => '', 'text' => '']];
        foreach ($models as $model) {
            $results[] = ['id' => $model->id, 'text' => $model->label];
        }
        return ['results' => $results];
    }

    /**
     * Finds the Component model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Component the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Component::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }


    /**
     * Updates an existing Component model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDearPush($id)
    {
        $model = $this->findModel($id);
        if (DearProduct::dearPush($id)) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Component was pushed to Dear.'));
        } else {
            Yii::$app->getSession()->addFlash('warning', Yii::t('app', 'Something went wrong when pushing Component to Dear:<br>{errors}', [
                'errors' => implode(', ', DearProduct::$pushErrors),
            ]));
        }
        return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
    }


    /**
     * @param null $id
     * @param bool $confirm
     * @return string
     */
    public function actionPrint($id = null, $confirm = false)
    {
        $post = Yii::$app->request->post();

        $model = new ComponentPrintForm();
        $model->ids = !empty($post['ids']) ? $post['ids'] : [];
        if ($id) {
            $model->ids[] = $id;
        }

        if ($confirm) {
            if ($model->load($post) && $model->save()) {
                Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Components have been printed.'));
                return $this->redirect(ReturnUrl::getUrl(['/']));
            }
        }

        return $this->render('print', [
            'model' => $model,
        ]);
    }
}
