<?php

namespace app\components\web;

use app\components\ReturnUrl;


/**
 * Class View
 * @package app\components\web
 */
class View extends \yii\web\View
{
    public $enableHints = false;

    public function renderPhpFile($_file_, $_params_ = [])
    {
        ReturnUrl::setUseCache(true);
        $originalContents = parent::renderPhpFile($_file_, $_params_);
        ReturnUrl::setUseCache(false);
        if ($this->enableHints) {
            $originalContents = $this->addHints($originalContents, $_file_, $_params_);
        }
        $replacedContents = $this->replaceReturnUrls($originalContents);
        return $replacedContents;

    }

    protected function replaceReturnUrls($originalContents)
    {
        if (!$originalContents) {
            return "";
        }
        $replace = [];
        if (strpos($originalContents, '_current_url_ru_place_holder') !== false) {
            if (empty(\Yii::$app->params['token'])) {
                \Yii::$app->params['token'] = ReturnUrl::getToken();
            }
            $replace['_current_url_ru_place_holder'] = \Yii::$app->params['token'];
        }

        $customUrlStart = strpos($originalContents, '_custom_url_start_');
        if ($customUrlStart !== false) {
            $customUrlEnd = strpos($originalContents, '_custom_url_end');
            $startPosition = $customUrlStart + 18;
            $encoded = substr($originalContents, $startPosition, $customUrlEnd - $startPosition);
            $url = @base64_decode($encoded);
            $url = $url ?: \Yii::$app->request->url;
            $token = ReturnUrl::urlToToken($url);
            $replace['_custom_url_start_' . $encoded . '_custom_url_end'] = $token;
        }
        if ($replace) {
            $replacedContent = str_replace(array_keys($replace), $replace, $originalContents);
        } else {
            $replacedContent = $originalContents;
        }
        return $replacedContent;
    }

    protected function hintsEnabled()
    {
        if (defined('YII_DEBUG') && YII_DEBUG && isset($_GET['mrphp_th']) && $_GET['mrphp_th']) {
            return true;
        }
        return false;
    }

    protected function addHints($originalContents, $_file_, $_params_)
    {
        if ($this->hintsEnabled()) {
            //inspired by magento hints
            $originalContents =
                <<<HTML
<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;">
<div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial;
text-align:left !important; z-index:998;" onmouseover="this.style.zIndex='999'"
onmouseout="this.style.zIndex='998'" title="{$_file_}">{$_file_}</div>
HTML
                . $originalContents . "</div>";
        }
        return $originalContents;
    }
}