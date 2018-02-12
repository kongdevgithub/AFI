<?php

namespace app\models\v3;

use \Yii;
use \yii\db\ActiveRecord;

class User extends ActiveRecord
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
        return 'user';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserToRoles()
    {
        return $this->hasMany(UserToRole::className(), ['user_id' => 'id']);
    }

}
