<?php

namespace app\widgets;

use Yii;
use yii\bootstrap\Widget;
use yii\web\View;

/**
 * JavaScript
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @copyright 2015 Mr PHP
 * @license BSD-3-Clause
 *
 * @usage
 * ```
 * <?php \app\widgets\JavaScript::begin(); ?>
 * <script>
 * ... your javascript ...
 * </script>
 * <?php \app\widgets\JavaScript::end(); ?>
 * ```
 */
class JavaScript extends Widget
{
    /**
     * @var
     */
    public $position;

    /**
     * @var
     */
    public $runOnAjax = true;

    /**
     *
     */
    public function init()
    {
        ob_start();
    }

    /**
     *
     */
    public function run()
    {
        // get position
        if ($this->position === null) {
            $this->position = View::POS_READY;
        }

        // get contents
        $js = ob_get_clean();

        // register the js script
        if ($this->runOnAjax || !Yii::$app->request->isAjax) {
            $js = str_replace(array('<script>', '<script type="text/javascript">', '</script>'), '', $js);
            $this->getView()->registerJs($js . ';', $this->position, $this->id);
        }
    }
}
