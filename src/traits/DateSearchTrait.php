<?php

namespace app\traits;

use app\components\ReturnUrl;
use app\models\query\ItemQuery;
use app\models\query\JobQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\web\Cookie;

/**
 *
 */
trait DateSearchTrait
{

    /**
     * @param ActiveQuery $query
     * @param $field
     * @param $date
     * @param string $type
     */
    protected function dateFilter($query, $field, $date, $type = 'date')
    {
        if (strpos($date, ' ')) {
            $date = explode(' ', $date);
            if ($type == 'date') {
                $query->andFilterWhere(['between', $field, $this->formatDate($date[0]), $this->formatDate($date[1])]);
            } else {
                $query->andFilterWhere(['between', $field, strtotime($this->formatDate($date[0])), strtotime($this->formatDate($date[1]))]);
            }
        } else {
            $this->andFilterDateCompare($query, $field, $date);
        }
    }

    /**
     * @param ActiveQuery $query
     * @param $name
     * @param $value
     */
    public function andFilterDateCompare($query, $name, $value)
    {
        if (preg_match('/^(<>|>=|>|<=|<|=)/', $value, $matches)) {
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
        } else {
            $operator = '=';
        }
        $query->andFilterWhere([$operator, $name, $this->formatDate($value)]);
    }

    /**
     * @param $date
     * @return array|string
     */
    public function formatDate($date)
    {
        $date = trim($date);
        return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
        //if (strpos($date, '/')) {
        //    $date = explode('/', $date);
        //    return $date[2] . '-' . $date[1] . '-' . $date[0];
        //}
        //return $date;
    }

}