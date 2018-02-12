<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Attachment;
use app\models\Log;
use app\components\ReturnUrl;
use app\models\search\AttachmentSearch;
use app\modules\goldoc\models\Product;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;

/**
 * This is the class for controller "AttachmentController".
 */
class AttachmentController extends Controller
{
    use AccessBehaviorTrait;

    //use TwoFactorTrait;

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';

        $post = Yii::$app->request->post();
        if (empty($_FILES['Attachment']['name']['upload']) || empty($post)) {
            return ['error' => 'No files found for upload.'];
        }

        $attachment = new Attachment();
        $attachment->model_name = empty($post['model_name']) ? '' : $post['model_name'];
        $attachment->model_id = empty($post['model_id']) ? '' : $post['model_id'];

        if (Yii::$app->user->can('staff')) {
        } elseif (Yii::$app->user->can('goldoc-goldoc') || Yii::$app->user->can('goldoc-active')) {
            if (!in_array($attachment->model_name, [Product::className()])) {
                return ['error' => 'Error while uploading images. Invalid permissions.'];
            }
        } else {
            if (!in_array($attachment->model_id, Yii::$app->user->identity->getClientJobs())) {
                return ['error' => 'Error while uploading images. Invalid permissions.'];
            }
        }

        // a flag to see if everything is ok
        $success = false;

        // file paths to store
        $paths = [];

        // process file
        if ($attachment->upload() && $attachment->save()) {
            $paths[] = $attachment->getFileUrl('300x300');
            $success = true;
        }

        // handle error
        if (!$success) {
            return ['error' => 'Error while uploading images. Contact the system administrator'];
        }

        // log
        Log::log('created attachment', $attachment->model_name, $attachment->model_id);

        // handle success
        return ['uploaded' => $paths];
    }

    /**
     * @inheritdoc
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('updated attachment', $model->model_name, $model->model_id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Attachment has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    /**
     *
     */
    public function actionSort()
    {
        if (Yii::$app->request->post('Attachment')) {
            foreach (Yii::$app->request->post('Attachment') as $k => $id) {
                $attachment = Attachment::findOne($id);
                $attachment->sort_order = $k;
                $attachment->save(false);
            }
        }
    }

    /**
     * Lists all Attachment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AttachmentSearch;
        $dataProvider = $searchModel->search($_GET);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Attachment model.
     * @param integer $id
     *
     * @return mixed
     * @throws HttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Attachment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Attachment;

        try {
            if ($model->load($_POST) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } elseif (!\Yii::$app->request->isPost) {
                $model->load($_GET);
            }
        } catch (\Exception $e) {
            $msg = (isset($e->errorInfo[2])) ? $e->errorInfo[2] : $e->getMessage();
            $model->addError('_exception', $msg);
        }
        return $this->render('create', ['model' => $model]);
    }


    /**
     * Deletes an existing Attachment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws HttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Attachment has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Attachment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Attachment the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Attachment::findOne($id)) !== null) {

            if (Yii::$app->user->can('staff')) {
            } elseif (Yii::$app->user->can('goldoc-goldoc') || Yii::$app->user->can('goldoc-active')) {
                if (!in_array($model->model_name, [Product::className()])) {
                    throw new HttpException(404, 'You do not have permission to the requested attachment.');
                }
            } else {
                if (!in_array($model->model_id, Yii::$app->user->identity->getClientJobs())) {
                    throw new HttpException(404, 'You do not have permission to the requested attachment.');
                }
            }

            return $model;
        } else {
            throw new HttpException(404, 'The requested page does not exist.');
        }
    }
}
