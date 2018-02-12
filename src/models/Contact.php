<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cebe\gravatar\Gravatar;
use cornernote\linkall\LinkAllBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contact".
 *
 * @mixin LinkBehavior
 * @mixin LinkAllBehavior
 *
 * @property HubSpotContact $hubSpotContact
 *
 * @property string $label
 */
class Contact extends base\Contact
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = LinkAllBehavior::className();
        $behaviors[] = [
            'class' => AuditTrailBehavior::className(),
            'ignored' => ['created_at', 'updated_at'],
        ];
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
        $rules[] = [['email'], 'required'];
        $rules[] = [['email'], 'unique', 'targetAttribute' => ['email', 'deleted_at']];
        $rules[] = [['merge_id'], 'required', 'when' => function ($model) {
            /** @var static $model */
            return $model->scenario == 'merge';
        }];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['merge'] = ['merge_id'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['default_company_id'] = Yii::t('app', 'Default Company');
        return $attributeLabels;
    }

    /**
     * @param int $size
     * @param array $options
     * @return string
     */
    public function getAvatar($size = 16, $options = [])
    {
        return Gravatar::widget([
            'email' => $this->email,
            'size' => $size,
            'options' => ArrayHelper::merge([
                'class' => 'img-circle',
            ], $options),
            'defaultImage' => 'mm',
        ]);
    }

    /**
     * @param bool $avatar
     * @return string
     */
    public function getLabel($avatar = false)
    {
        $avatar && $avatar = $this->getAvatar();
        return trim($avatar . ' ' . $this->first_name . ' ' . $this->last_name);
    }

    /**
     * @return string
     */
    public function getLabelWithEmail()
    {
        return $this->getLabel() . ' <' . $this->email . '>';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobs()
    {
        return $this->hasMany(Job::className(), ['contact_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('contact');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHubSpotContact()
    {
        return $this->hasOne(HubSpotContact::className(), ['model_id' => 'id']);
    }

    /**
     * @return query\LogQuery
     */
    public function getLogs()
    {
        $relation = Log::find();
        $relation->orOnCondition([
            'log.model_id' => $this->id,
            'log.model_name' => $this->className(),
        ]);
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails()
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => get_class($this),
        ]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Company::className(), ['id' => 'company_id'])
            ->viaTable(ContactToCompany::tableName(), ['contact_id' => 'id'])
            ->andWhere('company.deleted_at IS NULL');
        //->inverseOf('contacts');
        //->via('postToTag');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return implode(' | ', [
            'contact-' . $this->id . ': ' . $this->label,
        ]);
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return $this->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->default_company_id) {
            $contactToCompany = ContactToCompany::find()
                ->notDeleted()
                ->andWhere([
                    'contact_id' => $this->id,
                    'company_id' => $this->default_company_id,
                ])
                ->one();
            if (!$contactToCompany) {
                $contactToCompany = new ContactToCompany();
                $contactToCompany->contact_id = $this->id;
                $contactToCompany->company_id = $this->default_company_id;
                $contactToCompany->save();
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

}
