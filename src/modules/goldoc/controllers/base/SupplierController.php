<?php
/**
 * /vagrant/jobflw4/src/../runtime/giiant/358b0e44f1c1670b558e36588c267e47
 *
 * @package default
 */


namespace app\modules\goldoc\controllers\base;

use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\search\SupplierSearch;
use yii\web\Controller;
use Yii;
use yii\web\HttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use dmstr\bootstrap\Tabs;
use cornernote\returnurl\ReturnUrl;

/**
 * SupplierController implements the CRUD actions for Supplier model.
 */
class SupplierController extends Controller
{

	/**
	 * Lists all Supplier models.
	 *
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel = new SupplierSearch;
		$dataProvider = $searchModel->search(Yii::$app->request->get());

		return $this->render('index', [
				'dataProvider' => $dataProvider,
				'searchModel' => $searchModel,
			]);
	}


	/**
	 * Displays a single Supplier model.
	 *
	 * @param integer $id
	 * @return mixed
	 */
	public function actionView($id) {
		$model = $this->findModel($id);

		return $this->render('view', [
				'model' => $model,
			]);
	}


	/**
	 * Creates a new Supplier model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 *
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Supplier;
		$model->loadDefaultValues();

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Supplier has been created.'));
			return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
		} elseif (!Yii::$app->request->isPost) {
			$model->load(Yii::$app->request->get());
		}

		return $this->render('create', [
				'model' => $model,
			]);
	}


	/**
	 * Updates an existing Supplier model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Supplier has been updated.'));
			return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
		} elseif (!Yii::$app->request->isPost) {
			$model->load(Yii::$app->request->get());
		}

		return $this->render('update', [
				'model' => $model,
			]);
	}


	/**
	 * Deletes an existing Supplier model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$model = $this->findModel($id);

		$model->delete();
		Yii::$app->getSession()->addFlash('success', Yii::t('goldoc', 'Supplier has been deleted.'));
		return $this->redirect(ReturnUrl::getUrl(['index']));
	}


	/**
	 * Finds the Supplier model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @throws HttpException if the model cannot be found
	 * @param integer $id
	 * @return Supplier the loaded model
	 */
	protected function findModel($id) {
		if (($model = Supplier::findOne($id)) !== null) {
			return $model;
		}
		throw new HttpException(404, 'The requested page does not exist.');
	}


}
