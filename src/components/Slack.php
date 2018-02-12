<?php

namespace app\components;

use understeam\slack\Client;
use yii\helpers\Json;

/**
 * Class Slack
 */
class Slack extends Client
{

    /**
     * @var string
     */
    public $icon;

    /**
     * @param null $text
     * @param array $attachments
     * @param null $channel
     * @param null $username
     * @param null $icon
     */
    public function post($text = null, $attachments = [], $channel = null, $username = null, $icon = null)
    {
        $payload = [];
        if ($text !== false) {
            $payload['text'] = $text !== null ? $text : $this->defaultText;
        }
        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }
        if ($channel !== false) {
            $payload['channel'] = $channel !== null ? $channel : $this->defaultChannel;
        }
        if ($username !== false) {
            $payload['username'] = $username !== null ? $username : $this->username;
        }
        if ($icon !== false) {
            $payload['icon_url'] = $icon !== null ? $icon : $this->icon;
        }

        $this->httpclient->post($this->url, [
            'payload' => Json::encode($payload),
        ])->send();
    }

}