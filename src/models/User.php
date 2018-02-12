<?php

namespace app\models;

use app\components\LetterAvatar;
use app\models\query\UserQuery;
use bedezign\yii2\audit\AuditTrailBehavior;
use dektrium\rbac\models\Assignment;
use dektrium\user\helpers\Password;
use mar\eav\behaviors\EavBehavior;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "user".
 *
 * @mixin EavBehavior
 *
 * @property HubSpotUser $hubSpotUser
 *
 * @property string $label
 * @property Profile $profile
 * @property string $hub_spot_token
 * @property array $authy
 * @property array $two_factor
 * @property string $dynamic_menu
 * @property string $print_spool
 * @property array $job_print_item_types
 * @property array $page_size
 * @property string $page_limit
 * @property string $initials
 * @property string $skin
 * @property string $background
 * @property string $job_view
 *
 * @property string $quote_email_text
 * @property string $quote_greeting_text
 * @property string $quote_footer_text
 * @property string $quote_template
 * @property string $quote_totals_format
 *
 */
class User extends \dektrium\user\models\User
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['eav'] = [
            'class' => EavBehavior::className(),
            'modelAlias' => static::className(),
            'eavAttributesList' => [
                'authy' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'two_factor' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'hub_spot_token' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'dynamic_menu' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'print_spool' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'job_print_item_types' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'page_size' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                ],
                'page_limit' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'skin' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'background' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'job_view' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'quote_email_text' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'quote_greeting_text' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'quote_footer_text' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'quote_template' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                'quote_totals_format' => [
                    'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                ],
                //'eavProperty2' => [
                //    'rule' => [['eavProperty2'], 'integer'],
                //    'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY, // mode MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST - clear attribute value if no value like Classname[attributeName] in request, usefull for forms handling
                //    'modes' => [EavBehavior::MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST],
                //],
            ],
        ];
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }

    /**
     * Creates new user account. It generates password if it is not provided by user.
     * @return bool
     * @throws \Exception
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $transaction = $this->getDb()->beginTransaction();

        try {
            $this->password = $this->password == null ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_CREATE);

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->confirm();

            //$this->mailer->sendWelcomeMessage($this, null, true);
            $this->trigger(self::AFTER_CREATE);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * @param int $size
     * @param array $options
     * @return string
     */
    public function getAvatar($size = 16, $options = [])
    {
        $cacheKey = 'User.getAvatar.' . $this->id . '.' . $size . '.' . md5(Json::encode($options));
        $avatar = Yii::$app->cacheFile->get($cacheKey);
        if (!$avatar) {
            $img = new LetterAvatar($this->profile ? $this->profile->name : $this->username, 'circle', max($size, 32));
            $avatar = Html::img($img, ArrayHelper::merge([
                'width' => $size,
                'height' => $size,
                'class' => 'img-circle',
            ], $options));
            Yii::$app->cacheFile->set($cacheKey, $avatar);
        }
        return $avatar;
        //return Gravatar::widget([
        //    'email' => $this->profile->gravatar_email ?: $this->email,
        //    'options' => ArrayHelper::merge([
        //        'alt' => $this->username,
        //        'class' => 'img-circle',
        //    ], $options),
        //    'size' => $size,
        //    'defaultImage' => 'wavatar',
        //    'secure' => true,
        //]);
    }

    /**
     * @param int $size
     * @param array $options
     * @return string
     */
    public static function getSystemAvatar($size = 16, $options = [])
    {
        $cacheKey = 'User.getAvatar.0.' . $size . '.' . md5(Json::encode($options));
        $avatar = Yii::$app->cacheFile->get($cacheKey);
        if (!$avatar) {
            $avatar = Html::img(new LetterAvatar(Yii::$app->name, 'circle', max($size, 32)), ArrayHelper::merge([
                'width' => $size,
                'height' => $size,
                'class' => 'img-circle',
            ], $options));
            Yii::$app->cacheFile->set($cacheKey, $avatar);
        }
        return $avatar;
        //return Gravatar::widget([
        //    'email' => 'system@afibranding.com.au',
        //    'options' => ArrayHelper::merge([
        //        'alt' => 'system',
        //        'class' => 'img-circle',
        //    ], $options),
        //    'size' => $size,
        //    'defaultImage' => 'wavatar',
        //    'secure' => true,
        //]);
    }

    /**
     * @param bool $avatar
     * @return string
     */
    public function getLabel($avatar = false)
    {
        $avatar && $avatar = $this->getAvatar();
        return trim($avatar . ' ' . ($this->profile->name ? $this->profile->name : $this->username));
    }

    /**
     * @return string
     */
    public function getInitials()
    {
        $name = $this->profile ? $this->profile->name : $this->username;
        preg_match_all("/[A-Z]/", ucwords(strtolower($name)), $matches);
        return $matches ? implode('', $matches[0]) : '?';
    }

    /**
     * @inheritdoc
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @param string $id user_id from audit_entry table
     * @return mixed|string
     */
    public static function userIdentifierCallback($id)
    {
        $user = self::findOne($id);
        return $user ? Html::a($user->label, ['/user/admin/update', 'id' => $user->id]) : $id;
    }

    /**
     * @param string $identifier user_id from audit_entry table
     * @return mixed|string
     */
    public static function filterByUserIdentifierCallback($identifier)
    {
        return static::find()->select('id')
            ->where(['like', 'username', $identifier])
            ->orWhere(['like', 'email', $identifier])
            ->column();
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHubSpotUser()
    {
        return $this->hasOne(HubSpotUser::className(), ['model_id' => 'id']);
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return Html::a($this->getLabel(true), ['//user/profile/show', 'id' => $this->id], ['class' => 'modal-remote']);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        /** @var Assignment $assignment */
        $assignment = Yii::createObject(['class' => Assignment::className(), 'user_id' => $this->id]);
        return $assignment->items;
    }

    /**
     * @return array
     */
    public function getClientCompanies()
    {
        $companies = [];
        foreach (Yii::$app->authManager->getPermissionsByUser($this->id) as $permission) {
            if (substr($permission->name, 0, 9) == '_company_') {
                $companies[] = substr($permission->name, 9);
            }
        }
        return $companies;
    }


    /**
     * @return array
     */
    public function getClientJobs()
    {
        return Job::find()
            ->andWhere([
                'company_id' => $this->getClientCompanies(),
            ])
            ->createCommand()
            ->queryColumn();
    }

    /**
     * @return array
     */
    public static function optsSkin()
    {
        return [
            //'skin-black' => Yii::t('app', 'White'),
            'skin-blue' => Yii::t('app', 'Blue'),
            'skin-green' => Yii::t('app', 'Green'),
            'skin-purple' => Yii::t('app', 'Purple'),
            'skin-red' => Yii::t('app', 'Red'),
            'skin-yellow' => Yii::t('app', 'Yellow'),
        ];
    }

    /**
     * @return array
     */
    public static function optsBackground()
    {
        // http://lea.verou.me/css3patterns/
        return [
            'background-argyle' => Yii::t('app', 'Argyle'),
            'background-arrows' => Yii::t('app', 'Arrows'),
            'background-blueprint-grid' => Yii::t('app', 'Blueprint Grid'),
            'background-carbon-fibre' => Yii::t('app', 'Carbon Fibre'),
            'background-chocolate-weave' => Yii::t('app', 'Chocolate Weave'),
            'background-cross' => Yii::t('app', 'Cross'),
            'background-lined-paper' => Yii::t('app', 'Lined Paper'),
            'background-madraz' => Yii::t('app', 'Madraz'),
            //'background-microbial-mat' => Yii::t('app', 'Microbial Mat'),
            'background-rainbow-bokeh' => Yii::t('app', 'Rainbow bokeh'),
            'background-stairs' => Yii::t('app', 'Stairs'),
            //'background-tablecloth' => Yii::t('app', 'Tablecloth'),
            'background-tartan' => Yii::t('app', 'Tartan'),
            'background-waves' => Yii::t('app', 'Waves'),
            'background-starry-night' => Yii::t('app', 'Starry Night'),
            'background-weave' => Yii::t('app', 'Weave'),
        ];
    }

    /**
     * @return array
     */
    public static function optsJobView()
    {
        return [
            'quote' => Yii::t('app', 'Quote'),
            'production' => Yii::t('app', 'Production'),
            'despatch' => Yii::t('app', 'Despatch'),
        ];
    }

    public static function optsPageLimit()
    {
        return [
            10 => 10,
            25 => 25,
            100 => 100,
            250 => 250,
            1000 => 1000,
        ];
    }


}
