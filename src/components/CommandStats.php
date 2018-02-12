<?php

namespace app\components;


class CommandStats
{

    /**
     * @param int|null $i
     * @param int|null $count
     * @param bool $mem
     * @param bool|float $time
     * @return string
     */
    public static function stats($i = null, $count = null, $mem = true, $time = true)
    {
        $stats = '';
        if ($i && $count) {
            $stats .= '[done=' . floor($i / $count * 100) . '%|' . $i . '/' . $count . ']';
        }
        if ($mem) {
            $stats .= '[mem=' . number_format(memory_get_peak_usage() / 1024 / 1024, 1) . '|' . number_format(memory_get_usage() / 1024 / 1024, 1) . ']';
        }
        if ($time === true) {
            $stats .= '[time=' . number_format((microtime(true) - YII_BEGIN_TIME), 1) . ']';
        } elseif ($time) {
            $stats .= '[time=' . number_format($time, 1) . ']';
        }
        return $stats . ' ';
    }

}
