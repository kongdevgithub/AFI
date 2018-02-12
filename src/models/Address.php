<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;

/**
 * This is the model class for table "address".
 */
class Address extends base\Address
{
    /**
     *
     */
    const TYPE_BILLING = 'billing';
    /**
     *
     */
    const TYPE_SHIPPING = 'shipping';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        // no validation on model_name or model_id
        foreach ($rules as $k => $rule) {
            $fields = $rule[0];
            foreach ($fields as $kk => $field) {
                if (in_array($field, ['model_name']) || in_array($field, ['model_id'])) {
                    unset($fields[$kk]);
                }
                $rules[$k][0] = $fields;
            }
        }
        // custom validation on street
        $rules[] = [['street'], 'validateStreet'];
        $rules[] = [['city'], 'validateCity'];
        $rules[] = [['state'], 'validateState'];
        return $rules;
    }

    /**
     * @param $attribute
     */
    public function validateStreet($attribute)
    {
        foreach (explode("\n", $this->$attribute) as $k => $line) {
            if (strlen(trim($line)) > 30) {
                $this->addError($attribute, Yii::t('app', 'Line {line} cannot be more than 30 characters, please use more lines.', ['line' => ($k + 1)]));
            }
            if ($k + 1 == 4) {
                $this->addError($attribute, Yii::t('app', 'You cannot use more than 3 address lines.'));
            }
        }
    }

    /**
     * @param $attribute
     */
    public function validateCity($attribute)
    {
        if (!$this->postcode) {
            return;
        }
        $postcodes = Postcode::find()->andWhere(['postcode' => $this->postcode])->all();
        if (!$postcodes) {
            return;
        }
        foreach ($postcodes as $postcode) {
            if (strtoupper($this->$attribute) == strtoupper($postcode->city)) {
                return;
            }
        }
        $this->addError($attribute, Yii::t('app', 'City was not found in postcode {postcode}.', [
            'postcode' => $this->postcode,
        ]));
    }

    /**
     * @param $attribute
     */
    public function validateState($attribute)
    {
        if (!$this->postcode) {
            return;
        }
        $postcodes = Postcode::find()->andWhere(['postcode' => $this->postcode])->all();
        if (!$postcodes) {
            return;
        }
        foreach ($postcodes as $postcode) {
            if (strtoupper($this->$attribute) == strtoupper($postcode->state)) {
                return;
            }
        }
        $this->addError($attribute, Yii::t('app', 'State was not found in postcode {postcode}.', [
            'postcode' => $this->postcode,
        ]));
    }

    /**
     * @return array
     */
    public static function optsType()
    {
        return [
            self::TYPE_BILLING => Yii::t('app', 'Billing'),
            self::TYPE_SHIPPING => Yii::t('app', 'Shipping'),
        ];
    }

    /**
     * @param string $glue
     * @return string
     */
    public function getLabel($glue = ', ')
    {
        $pieces = [
            $this->name,
            trim($this->street),
            trim($this->city . ' ' . $this->postcode . ' ' . $this->state),
        ];
        if ($this->country != 'Australia') {
            $pieces[] = $this->country;
        }
        if ($this->contact) {
            $pieces[] = 'ATTN: ' . trim($this->contact);
        }
        if ($this->phone) {
            $pieces[] = 'PH: ' . trim($this->phone);
        }
        if ($this->instructions) {
            $pieces[] = trim($this->instructions);
        }
        return implode($glue, $pieces);
    }

    /**
     * @param array $attributes
     * @return Address|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $address = new Address();
        $address->loadDefaultValues();
        $address->attributes = $this->attributes;
        $address->id = null;
        $address->model_name = $attributes['Address']['model_name'];
        $address->model_id = $attributes['Address']['model_id'];
        $allowedAttributes = [
            'type',
        ];
        if (!empty($attributes['Address'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Address'])) {
                    $address->$attribute = $attributes['Address'][$attribute];
                }
            }
        }
        if (!$address->save(false)) {
            throw new Exception('cannot copy Address-' . $this->id . ': ' . Helper::getErrorString($address));
        }
        return $address;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Helper::clearRelatedCache($this);
        parent::afterSave($insert, $changedAttributes);
    }
}
