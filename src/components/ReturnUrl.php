<?php

namespace app\components;

use Yii;

/**
 * Class ReturnUrl
 */
class ReturnUrl extends \cornernote\returnurl\ReturnUrl
{
    public static $cacheSafe = false;

    /**
     * @inheritdoc
     */
    public static function cache()
    {
        return Yii::$app->cacheFile;
    }

    public static function getUseCache()
    {
        return false;
        return !empty(Yii::$app->params['cache']);
    }

    public static function setUseCache($value)
    {
        return Yii::$app->params['cache'] = $value;
    }

    public static function getToken()
    {
        //if caching enable return a place holder instead of token
        if (self::getUseCache()) {
            return '_current_url_ru_place_holder';
        }
        return parent::getToken();

    }

    public static function urlToToken($input)
    {
        /**
         * if cache is enable return encoded value enclosed in place holder
         *
         */
        if (self::getUseCache()) {
            $encoded = base64_encode($input);
            return '_custom_url_start_' . $encoded . '_custom_url_end';
        }
        return parent::urlToToken($input);
    }
}