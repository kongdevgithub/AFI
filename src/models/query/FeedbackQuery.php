<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Feedback]].
 *
 * @see \app\models\Feedback
 */
class FeedbackQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Feedback[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Feedback|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $field
     * @param $value
     */
    public function andFilterOperator($field, $value)
    {
        $operator = $this->getOperator($value);
        $operand = str_replace($operator, '', $value);
        $this->andFilterWhere([$operator, $field, $operand]);
    }

    /**
     * @param $qryString
     * @return string
     */
    private function getOperator($qryString)
    {
        switch ($qryString) {
            case strpos($qryString, '>=') === 0:
                $operator = '>=';
                break;
            case strpos($qryString, '>') === 0:
                $operator = '>';
                break;
            case strpos($qryString, '<=') === 0:
                $operator = '<=';
                break;
            case strpos($qryString, '<') === 0:
                $operator = '<';
                break;
            default:
                $operator = 'like';
                break;
        }
        return $operator;
    }

}