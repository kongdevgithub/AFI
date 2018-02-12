<?php

namespace app\components;

use mikehaertl\wkhtmlto\Pdf;
use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * PdfManager
 */
class PdfManager extends Component
{

    /**
     * @return Pdf
     */
    public static function getTest()
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.8cm',
            'margin-left' => '0cm',
            'header-html' => Yii::getAlias('@app/pdf/header-afi.html'),
            'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/print-spool/pdf');
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Job $model
     * @return Pdf
     */
    public static function getJobQuote($model)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '3.9cm',
            'margin-right' => '0cm',
            'margin-bottom' => '2.1cm',
            'margin-left' => '0cm',
            'header-html' => Yii::getAlias('@app/pdf/quote-header-' . $model->quote_template . '.html'),
            'footer-html' => Yii::getAlias('@app/pdf/quote-footer-' . $model->quote_template . '.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/job/quote-pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Job $model
     * @return Pdf
     */
    public static function getJobInvoice($model)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '4.5cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.8cm',
            'margin-left' => '0cm',
            'header-html' => Yii::getAlias('@app/pdf/invoice-header-afi.html'),
            'footer-html' => Yii::getAlias('@app/pdf/invoice-footer-afi.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/job/invoice-pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Job $model
     * @return Pdf
     */
    public static function getJobArtwork($model)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '0.5cm',
            'margin-right' => '0cm',
            'margin-bottom' => '0.5cm',
            'margin-left' => '0cm',
        ]);
        $html = Yii::$app->view->render('@app/views/job/artwork-pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Package $model
     * @return Pdf
     */
    public static function getPackage($model)
    {
        $headerFile = Yii::getAlias('@app/pdf/header-afi.html');
        $job = $model->getFirstJob();
        if ($job && $job->company->delivery_docket_header) {
            $headerFile = Yii::$app->runtimePath . '/pdf/header/company-' . $job->company->id . '.html';
            FileHelper::createDirectory(dirname($headerFile));
            file_put_contents($headerFile, implode("\n", [
                '<!DOCTYPE html>',
                '<html>',
                '<body style="border:0; margin: 0 50px;">',
                Html::tag('div', $job->company->delivery_docket_header, [
                    'style' => 'border-bottom: 1px solid #999',
                ]),
                '</body>',
                '</html>',
            ]));
        }
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.8cm',
            'margin-left' => '0cm',
            'header-html' => $headerFile,
            'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/package/pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Pickup $model
     * @return Pdf
     */
    public static function getPickup($model)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.8cm',
            'margin-left' => '0cm',
            'header-html' => Yii::getAlias('@app/pdf/header-afi.html'),
            'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/pickup/pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }


    /**
     * @param $view
     * @param $heading
     * @param $params
     * @return Pdf
     */
    public static function getDashboard($view, $heading, $params)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            'page-size' => 'A4',
            'margin-top' => '3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.2cm',
            'margin-left' => '0cm',
            'header-html' => Yii::getAlias('@app/pdf/header-afi.html'),
            //'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
            'footer-center' => 'Page [page] of [toPage]',
        ]);
        $html = Yii::$app->view->render('@app/views/dashboard/print', [
            'heading' => $heading,
            'view' => 'pages/' . $view,
            'params' => $params,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }

    /**
     * @param \app\models\Job $model
     * @param string|null $item_types
     * @return Pdf
     */
    public static function getJobProduction($model, $item_types = null)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '0.3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '1.3cm',
            'margin-left' => '0cm',
            //'header-html' => Yii::getAlias('@app/pdf/header-afi.html'),
            'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/job/production-pdf', [
            'model' => $model,
            'item_types' => $item_types,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }


    /**
     * @param \app\models\Item $model
     * @return Pdf
     */
    public static function getItemProduction($model)
    {
        $pdf = new Pdf([
            'use-xserver',
            'no-outline',
            'commandOptions' => [
                'enableXvfb' => true,
            ],
            'ignoreWarnings' => true,
            //'orientation' => 'landscape',
            'page-size' => 'A4',
            'margin-top' => '0.3cm',
            'margin-right' => '0cm',
            'margin-bottom' => '0.5cm',
            'margin-left' => '0cm',
            //'header-html' => Yii::getAlias('@app/pdf/header-afi.html'),
            'footer-html' => Yii::getAlias('@app/pdf/footer.html'),
        ]);
        $html = Yii::$app->view->render('@app/views/item/production-pdf', [
            'model' => $model,
        ]);
        $pdf->addPage($html);
        return $pdf;
    }
}
