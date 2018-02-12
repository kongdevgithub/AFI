<?php

namespace app\gearman;

use app\components\Helper;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\models\Package;
use app\models\Pickup;
use app\models\Product;
use app\models\Unit;
use app\models\User;
use Yii;
use yii\helpers\Url;

/**
 * LogSlackGearman
 */
class LogSlackGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        if (!YII_ENV_PROD)
            return;

        echo $params['id'];
        $log = Log::findOne($params['id']);
        if (!$log) {
            echo 'not found, skipping...';
            return;
        }
        echo ' - ' . $log->message . ' - ';

        $user = $log->created_by ? User::findOne($log->created_by) : false;
        $model = $this->getLogModel($log->model_name, $log->model_id);
        $modelString = $model ? ' <' . Url::to($model->getUrl(), 'https') . '|' . $this->getLogModelTitle($model) . '>' : '';
        $attachment = [];
        $attachment['text'] = $log->message . $modelString;
        if ($user) {
            $attachment['color'] = '#939354';
            $attachment['author_name'] = $user->label;
            $attachment['author_link'] = Url::to(['/user/profile/show', 'id' => $user->id], 'https');
            $attachment['author_icon'] = Helper::getUserAvatar($user);
        }
        Yii::$app->slack->post(false, [$attachment], 'console-log');

        echo 'done!';
    }

    /**
     * @param string|Job|Product|Item|Unit|Package|Pickup $modelName
     * @param int $id
     * @return bool|Job|Product|Item|Unit|Package|Pickup
     */
    private function getLogModel($modelName, $id)
    {
        return $id ? $modelName::findOne($id) : false;
    }

    /**
     * @param Job|Product|Item|Unit|Package|Pickup $model
     * @return bool|int|string
     */
    private function getLogModelTitle($model)
    {
        if (method_exists($model, 'getTitle')) {
            return $model->getTitle();
        }
        if (method_exists($model, 'getLinkText')) {
            return $model->getLinkText();
        }
        return $model->id;
    }
}