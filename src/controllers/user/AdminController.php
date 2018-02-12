<?php

namespace app\controllers\user;

use app\models\search\UserSearch;
use app\models\Target;
use app\models\User;
use app\traits\TwoFactorTrait;
use dektrium\user\controllers\AdminController as BaseAdminController;
use Yii;
use yii\helpers\Url;

/**
 * Class AdminController
 * @package app\controllers\user
 */
class AdminController extends BaseAdminController
{

    use TwoFactorTrait;

    public $layout = '@app/views/layouts/box';

    /**
     *
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        /** @var UserSearch $searchModel */
        $searchModel = Yii::createObject(UserSearch::className());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Updates targets for a users.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdateTargets($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);

        if (Yii::$app->request->post()) {
            foreach (Yii::$app->request->post('Target') as $date => $_target) {
                $target = Target::findOne([
                    'model_name' => $user->className(),
                    'model_id' => $user->id,
                    'date' => $date,
                ]);
                if (!$target) {
                    $target = new Target();
                    $target->model = $user->className();
                    $target->model_id = $user->id;
                    $target->date = $date;
                }
                if ($target->target != $_target) {
                    $target->target = $_target;
                    $target->save(false);
                }
            }
            Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Targets have been saved.'));
            return $this->refresh();
        }

        return $this->render('_targets', [
            'user' => $user,
        ]);
    }

    /**
     * Disabled two factor authentication.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDisableTwoFactor($id)
    {
        $user = User::findOne($id);
        $user->setEavAttribute('two_factor', null);
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Targets have been saved.'));
        return $this->redirect(['index']);
    }
}