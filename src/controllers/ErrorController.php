<?php

namespace app\controllers;

use yii\web\Controller;

/**
 * Error controller.
 */
class ErrorController extends Controller
{
    public $layout = '@app/views/layouts/narrow';

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
