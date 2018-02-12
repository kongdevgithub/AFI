<?php

namespace app\models\query;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\User]].
 *
 * @see \app\models\User
 */
class UserQuery extends ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/


    /**
     * Finds all users by assignment role
     *
     * @param  string $role
     * @return static
     */
    public function byRole($role)
    {
        return $this
            ->join('LEFT JOIN', 'auth_assignment', 'auth_assignment.user_id = id')
            ->where(['auth_assignment.item_name' => $role]);
    }

    /**
     * @inheritdoc
     * @return \app\models\User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}