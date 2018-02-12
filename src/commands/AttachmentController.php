<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\GearmanManager;
use app\components\Helper;
use app\models\Attachment;
use app\models\HubSpotDeal;
use app\models\Job;
use Yii;
use yii\console\Controller;

/**
 * Class AttachmentController
 * @package app\commands
 */
class AttachmentController extends Controller
{


    /**
     *
     */
    public function actionS3Upload()
    {
        $this->stdout('FINDING ATTACHMENTS' . "\n");
        $query = Attachment::find();
        $count = $query->count();

        foreach ($query->each(100) as $k => $model) {
            /** @var Attachment $model */
            $this->stdout(CommandStats::stats($k + 1, $count));
            $this->stdout('uploading id:' . $model->id . ' - ');

            $path = $model->getFilePath();
            $src = $model->getFileSrc();
            if (!file_exists($path)) {
                $this->stdout('not found, skipping' . "\n");
                continue;
            }
            $this->stdout('from ' . $path . ' to ' . $src . "\n");
            Yii::$app->s3->upload($src, $path);
            $model->thumb();

        }
        $this->stdout('DONE!' . "\n");
    }

}
