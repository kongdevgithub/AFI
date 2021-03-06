<?php

namespace app\components;

use Yii;

/**
 * User
 *
 * @property \app\models\User $identity
 */
class User extends \dmstr\web\User
{
    /**
     * @inheritdoc
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if (Yii::$app instanceof \yii\console\Application) {
            return true;
        }
        return parent::can($permissionName, $params, $allowCaching);
    }

    /**
     * @inheritdoc
     */
    public function getIdentity($autoRenew = true)
    {
        if (Yii::$app instanceof \yii\console\Application) {
            return null;
        }
        return parent::getIdentity($autoRenew);
    }
}