Simple EAV
==========
simple way to add to your application dynamic attributes to every model you wish

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mar/yii2-simple-eav "*"
```

or add

```
"mar/yii2-simple-eav": "*"
```

to the require section of your `composer.json` file.

execute migrations from migration folder of extension


Usage
-----

Once the extension is installed, simply use it in your code by  :

1. Add behavior,alias and validation rules to your model like this:
 ( you can use any alias it will be used to identify your $model::className in db
    if you will change alias, old attribute values will disappear =))
  )

    public function behaviors()
    {
        return [
            [
                'class' => \mar\eav\behaviors\EavBehavior::className(),
                'modelAlias' => 'product',
                'eavAttributesList' => [
                    'eavProperty1' => [
                        'rule' => [['eavProperty1'], 'string', 'max' => 255],
                        'type' => EavBehavior::ATTRIBUTE_TYPE_TEXT,
                    ],
                    'eavProperty2' => [
                        'rule' => [['eavProperty2'], 'integer'],
                        'type' => EavBehavior::ATTRIBUTE_TYPE_ARRAY,
                        // mode MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST - clear attribute value if no value like Classname[attributeName] in request, usefull for forms handling
                        'modes' => [
                                                    EavBehavior::MODE_SET_EMPTY_IF_NO_VALUE_IN_REQUEST
                        ]
                    ],
                    .....
                ]
            ]

        ];
    }

2. If you wish you can add labels for eav properties to attributeLabels() method of your model

3.That's all, you cat access your new property like $model->eavProperty1, also you can use it in form like this :
        <?= $form->field($model, 'eavProperty1')->textInput(['maxlength' => true]) ?>

PS:
1)attributes and values will be removed if you delete them from behavior config

possible future updates:
    1. search by eav fields
    2. relations by eav fields
    3. using category_id to bind eav attributes to class by category