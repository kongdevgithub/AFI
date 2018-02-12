<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Log;
use app\models\Note;
use app\components\ReturnUrl;
use app\models\search\NoteSearch;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;

/**
 * This is the class for controller "app\controllers\NoteController".
 */
class NoteController extends Controller
{
    use AccessBehaviorTrait;
    //use TwoFactorTrait;

    public function actionCreate()
    {
        $model = new Note;
        $model->scenario = 'create';
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('created note', $model->model_name, $model->model_id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Note has been created.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('create', compact('model'));
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::log('updated note', $model->model_name, $model->model_id);
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Note has been updated.'));
            return $this->redirect(ReturnUrl::getUrl(['view', 'id' => $model->id]));
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', compact('model'));
    }

    public function actionSort()
    {
        if (Yii::$app->request->post('Note')) {
            foreach (Yii::$app->request->post('Note') as $k => $id) {
                $note = Note::findOne($id);
                $note->sort_order = $k;
                $note->save(false);
            }
        }
    }

    /**
     * Lists all Note models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NoteSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Note model.
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
     * Deletes an existing Note model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Note has been deleted.'));

        return $this->redirect(ReturnUrl::getUrl(['index']));
    }

    /**
     * Finds the Note model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Note the loaded model
     * @throws HttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Note::findOne($id)) !== null) {
            return $model;
        }
        throw new HttpException(404, 'The requested page does not exist.');
    }
}
