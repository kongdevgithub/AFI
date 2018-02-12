<?php

namespace app\models\v3;

use \Yii;
use \yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->dbV3;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role';
    }

}
