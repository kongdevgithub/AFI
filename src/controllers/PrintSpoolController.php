<?php

namespace app\controllers;

use app\components\Controller;
use app\components\PrintSpool;
use app\models\form\TestPrintForm;
use app\components\ReturnUrl;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * PrintSpoolController controller.
 */
class PrintSpoolController extends Controller
{

    /**
     * @param $spool
     * @return \yii\web\Response
     */
    public function actionView($spool)
    {
        $spool = basename(trim($spool, '/'));
        $files = [];
        $_files = FileHelper::findFiles(PrintSpool::getPath($spool));
        asort($_files);
        foreach ($_files as $file) {
            $file = basename($file);
            $files[] = Url::to(['//print-spool/download', 'spool' => $spool, 'file' => $file], 'https');
        }
        return implode(' ', $files);
    }

    /**
     * @param $spool
     * @return \yii\web\Response|string
     */
    public function actionCount($spool)
    {
        $spool = basename(trim($spool, '/'));
        return PrintSpool::setCountFile($spool);
    }

    /**
     * @param $spool
     * @param $file
     * @return \yii\web\Response
     */
    public function actionDownload($spool, $file)
    {
        $spool = trim($spool, '/');
        $this->layout = false;
        $file = $this->getFile($spool, $file);
        $contents = file_get_contents($file);
        unlink($file);
        PrintSpool::setCountFile($spool);
        //header("Pragma: public"); // required
        //header("Expires: 0");
        //header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        //header("Cache-Control: private", false); // required for certain browsers
        //header("Content-Type: application/force-download");
        //header("Content-Disposition: attachment; filename=\"" . basename($file) . "\";");
        //header("Content-Transfer-Encoding: binary");
        //header("Content-Length: " . filesize($file));
        return Yii::$app->response->sendContentAsFile($contents, basename($file));
    }

    /**
     * @param $spool
     * @param $file
     */
    public function actionDelete($spool, $file)
    {
        $spool = basename(trim($spool, '/'));
        $file = $this->getFile($spool, $file);
        if (file_exists($file)) {
            unlink($file);
        }
        PrintSpool::setCountFile($spool);
    }

    /**
     * get full file path based on url request, eg:
     * /printSpool/download/spool/MRPHP/folder/file.txt
     *
     * @param $spool
     * @param $file
     * @return string
     */
    protected function getFile($spool, $file)
    {
        return PrintSpool::getPath($spool) . '/' . $file;
    }


    /**
     * @return string
     */
    public function actionTest()
    {
        $model = new TestPrintForm;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->getSession()->addFlash('success', Yii::t('app', 'Tests have been printed.'));
            return $this->redirect(ReturnUrl::getUrl(['/print-spool/test']));
        }
        return $this->render('test', [
            'model' => $model,
        ]);
    }

}
