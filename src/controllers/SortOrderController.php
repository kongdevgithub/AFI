<?php

namespace app\controllers;

use app\components\Controller;
use app\components\ReturnUrl;
use app\models\SortOrder;
use app\traits\AccessBehaviorTrait;
use app\traits\TwoFactorTrait;
use Yii;

/**
 * Class SortOrderController
 * @package app\controllers
 */
class SortOrderController extends Controller
{

    use AccessBehaviorTrait;
    use TwoFactorTrait;

    public $enableCsrfValidation = false;

    /**
     * Handles the ordering of model.
     * @param $model
     */
    public function actionSort($model)
    {
        $data = Yii::$app->request->post($model);
        $data = array_reverse($data);
        if ($data) {
            foreach ($data as $k => $id) {
                $sortOrder = SortOrder::findOne(['model_name' => $model, 'model_id' => $id]);
                if (!$sortOrder) {
                    $sortOrder = new SortOrder();
                    $sortOrder->model_name = $model;
                    $sortOrder->model_id = $id;
                }
                $sortOrder->sort_order = $k;
                $sortOrder->save(false);
            }
        }
    }

    /**
     * Resets the order of a sorted list
     * @param $model
     * @return \yii\web\Response
     */
    public function actionReset($model)
    {
        foreach (SortOrder::findAll(['model' => $model]) as $sortOrder) {
            $sortOrder->delete();
        }
        return $this->redirect(ReturnUrl::getUrl());
    }

    /**
     * Move a model to the top of a sorted list.
     * @param $id
     * @param $model
     */
    public function actionTop($id, $model)
    {
        $max_sort_order = 0;
        $maxSortOrder = SortOrder::find('model=:model ORDER BY sort_order DESC', array('model' => $model));
        if ($maxSortOrder) {
            $max_sort_order = $maxSortOrder->sort_order + 1;
        }
        $sortOrder = SortOrder::find('model=:model AND foreign_key=:foreign_key', array('model' => $model, 'foreign_key' => $id));
        if (!$sortOrder) {
            $sortOrder = new SortOrder;
            $sortOrder->model = $model;
            $sortOrder->foreign_key = $id;
        }
        $sortOrder->sort_order = $max_sort_order;
        $sortOrder->save();
        $this->redirect(ReturnUrl::getUrl());
    }

    /**
     * @param $id
     * @param $model
     */
    public function actionBottom($id, $model)
    {
        $min_sort_order = 0;
        $minSortOrder = SortOrder::find('model=:model ORDER BY sort_order ASC', array('model' => $model));
        if ($minSortOrder) {
            $min_sort_order = $minSortOrder->sort_order - 1;
        }
        $sortOrder = SortOrder::find('model=:model AND foreign_key=:foreign_key', array('model' => $model, 'foreign_key' => $id));
        if (!$sortOrder) {
            $sortOrder = new SortOrder;
            $sortOrder->model = $model;
            $sortOrder->foreign_key = $id;
        }
        $sortOrder->sort_order = $min_sort_order;
        $sortOrder->save();
        $this->redirect(ReturnUrl::getUrl());
    }


}
