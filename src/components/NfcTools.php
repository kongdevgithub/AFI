<?php

namespace app\components;

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class NfcTools
 *
 * Requires the following to be installed on the android device:
 * https://play.google.com/store/apps/details?id=com.wakdev.wdnfc
 *
 * demo: http://try.api.nfc.systems/
 * guide: http://www.wakdev.com/en/apps/nfc-tools/api-nfc-tools-english.html
 * source: https://github.com/wakdev/nfctools-api/blob/master/webapp-get-example.php
 *
 *
 * @package app\components
 */
class NfcTools
{
    /**
     * @param $url
     * @param array $params
     * @return string
     */
    public static function scanUrl($url, $params = [])
    {
        $params || $params = [
            'TAG-ID',
            'TAG-SIZE',
            'TAG-MAXSIZE',
            'TAG-TYPE',
            'TAG-ISWRITABLE',
            'TAG-CANMAKEREADONLY',
            'NDEF-TEXT',
            'NDEF-URI',
        ];
        $urlParams = $url;
        foreach ($params as $param) {
            $urlParams[strtolower($param)] = '{' . $param . '}';
        }
        return 'nfc://scan/?callback=' . urlencode(urldecode(Url::to($urlParams, 'https')));
    }

    /**
     * @param $url
     * @return string
     */
    public static function scanButton($url)
    {
        return Html::a('RFID Scanner', NfcTools::scanUrl($url), ['class' => 'btn btn-default']);
    }
}

// nfc://scan/?callback=http%3A%2F%2Ftry.api.nfc.systems%2F    %3Ftag-id%3D%7BTAG-ID%7D%26tagsize%3D%7BTAG-SIZE%7D%26tagmaxsize%3D%7BTAG-MAXSIZE%7D%26tagtype%3D%7BTAG-TYPE%7D%26tagiswritable%3D%7BTAG-ISWRITABLE%7D%26tagcanmakereadonly%3D%7BTAG-CANMAKEREADONLY%7D%26ndef-text%3D%7BNDEF-TEXT%7D%26ndef-uri%3D%7BNDEF-URI%7D%23result
// nfc://scan/?callback=https%3A%2F%2Fdev.afi.ink%2Fnfc%2Findex%3Ftag-id%3D%257BTAG-ID%257D%26tag-size%3D%257BTAG-SIZE%257D%26tag-maxsize%3D%257BTAG-MAXSIZE%257D%26tag-type%3D%257BTAG-TYPE%257D%26tag-iswritable%3D%257BTAG-ISWRITABLE%257D%26tag-canmakereadonly%3D%257BTAG-CANMAKEREADONLY%257D%26ndef-text%3D%257BNDEF-TEXT%257D%26ndef-uri%3D%257BNDEF-URI%257D%23result
