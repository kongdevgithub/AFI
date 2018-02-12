<?php

namespace mar\eav\behaviors;

use mar\eav\models\EavAttribute;
use mar\eav\models\EavAttributeValue;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class EavBehavior extends Behavior
{
    /** eav property type */
    /** simple text */
    const ATTRIBUTE_TYPE_TEXT = 1;
    /** will bet serialized before save and unserialized on __get($property) */
    const ATTRIBUTE_TYPE_ARRAY = 2;

    /**set empty if not set in Yii::$app->request */
    const MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST = 1;


    /** @var ActiveRecord $this ->owner */
    /** @var  string - alias which will be used to identify model class in db */
    public $modelAlias;
    /** @var  array in forman ['name' => $label] */
    public $eavAttributesList;

    /**
     * set Eav attribute by It's name
     * @param string $name
     * @param mixed $value
     * @return boolean
     * */
    public function setEavAttribute($name, $value)
    {
        /** @var EavAttribute $eavAttribute */
        if ($eavAttribute = $this->getEavAttributeModel($name, $this->modelAlias)) {
            if ($this->isArrayType($name)) {
                $value = serialize($value);
            }
            $eavAttribute->setValue($this->getWorkModel(), $value);
        }
    }

    /**
     * get Eav attribute by It's name
     * @param string $name
     * @return mixed
     * */
    public function getEavAttribute($name)
    {
        /** @var EavAttribute $eavAttribute */
        if ($eavAttribute = $this->getEavAttributeModel($name, $this->modelAlias)) {
            $value = $eavAttribute->getValue($this->getWorkModel());
            if ($this->isArrayType($name)) {
                $value = unserialize($value);
            }
            return $value;
        }
        return null;
    }

    /**
     * get eav attributes as array ['name' => 'value']
     * @return array
     * */
    public function getEavAttributes()
    {
        /** @var EavAttribute $attributesArray */
        $attributesArray = [];
        /**
         * @var string $name
         * @var array $value
         */
        foreach ($this->eavAttributesList as $name => $value) {
            /** @var EavAttribute[] $attributes */
            $attributesArray[$name] = $this->getEavAttribute($name);
        }
        return $attributesArray;
    }

    /**
     * set eav attributes , input param should be array ['name' => 'value']
     * */
    public function setEavAttributes($values)
    {
        /** @var EavAttribute[] $attributes */
        $attributes = EavAttribute::find()->where([
            'alias' => $this->modelAlias,
        ])->all();
        foreach ($attributes as $k => $attribute) {
            if (!empty($values[$attribute->name]) && array_key_exists($attribute->name, $this->eavAttributesList)) {
                $this->setEavAttribute($attribute->name, $values[$attribute->name]);
            }
            //Todo: if attribute is not actual ( !array_key_exists($value, $this->eavAttributesList) )
        }
        //Todo:  errors notification
    }


    /**
     * return eav attribute model for current model
     * @param string $name -attr name
     * @param string $alias - alias to  model::className
     * @return EavAttribute
     *
     * */
    protected function getEavAttributeModel($name, $alias)
    {
        /** @var EavAttribute $attribute */
        $attribute = EavAttribute::find()->where([
            'alias' => $alias,
            'name' => $name,
        ])->one();

        if (!empty($attribute)) {
            return $attribute;
        } else {
            /** @var EavAttribute $attribute */
            $attribute = new EavAttribute();
            $attribute->name = $name;
            $attribute->alias = $alias;
            //Todo:: make smth with labels
            $attribute->label = '';
            if ($attribute->save()) {
                return $attribute;
            }
        }
        return null;
    }

    /**
     * return return model which should has eav attributes ( change to $this->owner if use as a behavior)
     * @return ActiveRecord;
     * */
    public function getWorkModel()
    {
        return $this->owner;
    }

    public function attach($owner)
    {
        //clean old attributes which are not actual according current behavior config
        $this->removeNotActualEavAttributes();
        // to attach events
        parent::attach($owner);
        // add validation rules
        $validators = $owner->validators;
        foreach ($this->getEavAttributesRules() as $rule) {
            if ($rule instanceof yii\validators\Validator) {
                $validators->append($rule);
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $validator = yii\validators\Validator::createValidator($rule[1], $owner, (array)$rule[0], array_slice($rule, 2));
                $validators->append($validator);
            } else {
                throw new yii\base\InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
    }

    public function events()
    {
        //Todo: add attributes before search or something to find by eav attributes
        return [
//            ActiveRecord::EVENT_BEFORE_INSERT => 'afterSaveUpdates',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSaveUpdates',
//            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSaveUpdates',
        ];
    }

    /**
     * something we shoud do after update or insert owner model
     * @param $event yii\base\Event
     * */
    public function beforeSaveUpdates($event)
    {

        foreach ($this->eavAttributesList as $name => $attrArr) {
            if (!empty($attrArr['modes']) && in_array(EavBehavior::MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST, $attrArr['modes'])) {
                $bodyParam = Yii::$app->getRequest()->getBodyParam($this->getScope());
                if ($bodyParam && empty($bodyParam[$name])) {
                    $this->setEavAttribute($name, '');
                }
            }
        }
    }

    /**
     * something we shoud do after update or insert owner model
     * @param $event yii\base\Event
     * */
    public function afterSaveUpdates($event)
    {
        $this->owner->afterSave($event->name === 'afterInsert', $this->owner->oldAttributes);
    }



    /** try to get scope ( form name ) which is used in $_REQEST while update owner model
     * @return string
     * */
    protected function getScope()
    {
        $scope = '';
        $ownerClassName = $this->owner->className();
        $ownerClassNameArr = explode('\\', $ownerClassName);
        if (is_array($ownerClassNameArr) && count($ownerClassNameArr)) {
            $scope = array_pop($ownerClassNameArr);
        }
        return $scope;
    }


    /**
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->eavAttributesList)) {
            $this->setEavAttribute($name, $value);
            return;
        }
        parent::__set($name, $value);
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     * @param string $name property name
     * @param mixed $value property value
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->eavAttributesList)) {
            return $this->getEavAttribute($name);
        }
        return parent::__get($name);
    }


    public function attributes()
    {
        $names = parent::attributes(); // TODO: Change the autogenerated stubvs
        foreach ($this->eavAttributesList as $name => $value) {
            $names[] = $name;
        }
        return $names;
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, $this->getEavAttributes())) {
            return true;
        }
        return false;
    }

    public function canSetProperty($name, $checkVars = true)
    {
        if (array_key_exists($name, $this->getEavAttributes())) {
            return true;
        }
        return false;
    }

    /**
     * check if function exists in  $this->eavAttributesList;>
     * @param string $name
     * @return boolean
     * */
    protected function existsInList($name)
    {
        if (array_key_exists($name, $this->eavAttributesList)) {
            return true;
        }
        return false;
    }

    /**
     * get array with rules for each eav attribute
     * @return array
     */
    public function getEavAttributesRules()
    {
        $rules = [];
        foreach ($this->eavAttributesList as $name => $attrArr) {
            if (!empty($attrArr['rule']))
                $rules[] = $attrArr['rule'];
            else
                $rules[] = [[$name], 'safe'];
        }
        return $rules;
    }


    /** return id of model to bind eav attribute value */
    protected function getObjectId()
    {
        return $this->id;
    }

    /** check if attribute declared as array type
     * @param string $name
     * @return boolean
     * */
    protected function isArrayType($name)
    {
        if (isset($this->eavAttributesList[$name]['type'])
            && $this->eavAttributesList[$name]['type'] === EavBehavior::ATTRIBUTE_TYPE_ARRAY
        ) {
            return true;
        }
        return false;
    }

    /** remove from db all eav attributes for this model whick are mot actual ( not in     public $eavAttributesList ) */
    protected function  removeNotActualEavAttributes()
    {
        $eavAttributes = array_keys($this->eavAttributesList);
        //$eavAttributes = ['one', 'two'];
        /** @var EavAttribute[] $notActualAttributes */
        $notActualAttributes = EavAttribute::find()->where([
            'alias' => $this->modelAlias,
        ])->andWhere(['not in', 'name', $eavAttributes])->all();

        foreach ($notActualAttributes as $key => $attribute) {
            $attribute->delete();
        }
    }
}