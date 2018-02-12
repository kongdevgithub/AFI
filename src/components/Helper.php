<?php

namespace app\components;

use app\models\AccountTerm;
use app\models\Address;
use app\models\Attachment;
use app\models\Company;
use app\models\Component;
use app\models\Contact;
use app\models\Industry;
use app\models\Item;
use app\models\ItemType;
use app\models\Job;
use app\models\JobType;
use app\models\Note;
use app\models\Notification;
use app\models\Option;
use app\models\Package;
use app\models\Pickup;
use app\models\PriceStructure;
use app\models\Product;
use app\models\ProductType;
use app\models\Size;
use bedezign\yii2\audit\models\AuditEntry;
use cornernote\cachebehavior\CacheBehavior;
use DateInterval;
use DateTime;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * Helper
 * @package app\components
 */
class Helper
{
    /**
     * Returns a string based on the model errors
     *
     * @param Model|Model[] $models
     * @return string
     */
    public static function getErrorString($models)
    {
        $output = array();
        if (!is_array($models)) $models = [$models];
        foreach ($models as $model) {
            if ($model->errors) {
                $modelOutput = [$model::className()];
                foreach ($model->errors as $attribute => $errors) {
                    $modelOutput[] = $attribute . ': ' . implode('; ', $errors);
                }
                $output[] = implode(' | ', $modelOutput);
            }
        }
        return implode(' || ', $output);
    }

    /**
     * @param $statusList
     * @return string
     */
    public static function getStatusButtonGroup($statusList)
    {
        $quantity = 0;
        foreach ($statusList as $count) {
            $quantity += $count;
        }
        $buttons = [];
        foreach ($statusList as $status => $count) {
            $buttons[] = Helper::getStatusButton($status, $count);
        }
        return '<div class="btn-group">' . implode('', $buttons) . '</div>';
    }

    /**
     * @param $status
     * @param $count
     * @return string
     */
    public static function getStatusButton($status, $count = null)
    {
        $workflowStatus = Yii::$app->workflowSource->getStatus($status);
        $metadata = $workflowStatus->getMetadata();
        $background = !empty($metadata['background']) ? 'background: ' . $metadata['background'] . ';' : '';
        $title = Inflector::humanize(explode('/', $status)[0]) . ' ' . $workflowStatus->getLabel() . ($count ? ' (' . $count . ')' : '');
        $color = !empty($metadata['color']) ? 'color: ' . $metadata['color'] . ';' : '';
        $icon = !empty($metadata['icon']) ? '<span class="' . $metadata['icon'] . '"></span>' : $workflowStatus->getLabel();
        if ($count) {
            $icon .= '<span class="label label-primary">' . $count . '</span>';
        }
        return Html::button($icon, [
            'encodeLabel' => false,
            'class' => 'btn btn-default btn-sm btn-status',
            'style' => $background . $color . ' cursor: help;',
            'title' => $title,
            'data-toggle' => 'tooltip',
        ]);
    }


    /**
     * Returns a gradient between fixed discount factors
     *
     * @param float $amount
     * @param string|array $scales
     * @return int|mixed
     */
    public static function getAmountBetweenScale($amount, $scales)
    {
        $amountScales = static::getAmountScales($scales);
        $keys = array_keys($amountScales);
        $amountFrom = reset($keys);
        $scaleFrom = reset($amountScales);
        $amountTo = end(array_keys($amountScales));
        $scaleTo = end($amountScales);
        foreach ($amountScales as $_amount => $scale) {
            if ($amount >= $_amount) {
                $amountFrom = $_amount;
                $scaleFrom = $scale;
                if ($scaleTo == end($amountScales)) {
                    $amountTo = $_amount;
                    $scaleTo = $scale;
                }
                break;
            }
            $amountTo = $_amount;
            $scaleTo = $scale;
        }
        if (($amountTo - $amountFrom) && ($amount - $amountFrom)) {
            $scaleFrom -= ($scaleFrom - $scaleTo) / (($amountTo - $amountFrom) / ($amount - $amountFrom));
        }
        return round($scaleFrom, 4);
    }

    /**
     * @param string $scales
     * @return array
     */
    protected static function getAmountScales($scales)
    {
        if (is_array($scales)) {
            krsort($scales);
            return $scales;
        }
        $amountScales = [];
        if (trim($scales)) {
            foreach (explode("\n", trim($scales)) as $k => $scale) {
                $scale = explode(' ', trim($scale));
                $amountScales[$scale[0]] = $scale[1];
            }
        }
        krsort($amountScales);
        return $amountScales;
    }


    /**
     * @param $size
     * @return mixed
     */
    public static function getSizeHtml($size)
    {
        $label = [];

        // find from size table
        if (isset($size['value'])) {
            $_size = Size::findOne($size['value']);
            if ($_size) {
                if ($_size->width) {
                    $size['width'] = $_size->width;
                }
                if ($_size->height) {
                    $size['height'] = $_size->height;
                }
                if ($_size->depth) {
                    $size['depth'] = $_size->depth;
                }
                if ($_size->label) {
                    $label[] = $_size->label;
                }
            }
            unset($size['value']);
        }

        // set the label
        if (!$label) {
            if (!empty($size['width'])) {
                $label[] = $size['width'] . 'W';
            }
            if (!empty($size['height'])) {
                $label[] = $size['height'] . 'H';
            }
            if (!empty($size['depth'])) {
                $label[] = $size['depth'] . 'D';
            }
        }
        return implode('x', $label);
    }

    /**
     * @param $size
     * @return string
     */
    public static function getAreaHtml($size)
    {
        return round(($size['width'] / 1000) * ($size['height'] / 1000), 3) . 'm<sup>2</sup>';
    }

    /**
     * @param $size
     * @return string
     */
    public static function getPerimeterHtml($size)
    {
        if (!empty($size['depth'])) {
            return round((($size['width'] / 1000) + ($size['height'] / 1000) + ($size['depth'] / 1000)) * 4, 3) . 'm';
        }
        return round((($size['width'] / 1000) + ($size['height'] / 1000)) * 2, 3) . 'm';
    }

    /**
     * @param $curve
     * @return mixed
     */
    public static function getCurveHtml($curve)
    {
        $label = [];
        if (!empty($curve['type'])) {
            $label[] = $curve['type'];
            if ($curve['type'] == 'cylinder') {
                if (!empty($curve['direction'])) {
                    $label[] = $curve['direction'];
                }
                if (!empty($curve['toe'])) {
                    $label[] = $curve['toe'];
                }
                if (!empty($curve['diameter'])) {
                    $label[] = ($curve['diameter'] / 2) . 'R';
                }
                if (!empty($curve['length'])) {
                    $label[] = $curve['length'] . 'L';
                }
                if (!empty($curve['degrees'])) {
                    $label[] = $curve['degrees'] . '&deg;';
                }
            } elseif ($curve['type'] == 'circle') {
                if (!empty($curve['diameter'])) {
                    $label[] = ($curve['diameter'] / 2) . 'R';
                }
                if (!empty($curve['length'])) {
                    $label[] = $curve['length'] . 'L';
                }
            }
        }
        return implode(' ', $label);

//        // set some extra info
//        if (!empty($curve['width']) && !empty($curve['height'])) {
//            //$quantity = $productToOption->product->quantity;
//            $output[] = '<small>' . implode(' | ', [
//                    Yii::t('app', 'Area') . ': ' . round(($curve['width'] / 1000) * ($curve['height'] / 1000), 3) . 'm<sup>2</sup>',
//                    Yii::t('app', 'Perimeter') . ': ' . round((($curve['width'] / 1000) + ($curve['height'] / 1000)) * 2, 3) . 'm',
//                ]) . '</small>';
//            //$_size[] = '<small>Total: ' . implode(' | ', [
//            //        Yii::t('app', 'Area') . ': ' . round(($value['width'] / 1000) * ($value['height'] / 1000) * $quantity, 3) . 'm<sup>2</sup>',
//            //        Yii::t('app', 'Perimeter') . ': ' . round((($value['width'] / 1000) + ($value['height'] / 1000)) * 2 * $quantity, 3) . 'm',
//            //    ]) . '</small>';
//        }
//        return implode('<br>', $output);
    }

    /**
     * @param $model_name
     * @param $field
     * @param $value
     * @return string
     */
    public static function getAuditTrailValue($model_name, $field, $value)
    {
        if (!$value) {
            return '';
        }
        if ($field == 'job_id') {
            $model = Job::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'product_id') {
            $model = Product::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'item_id') {
            $model = Item::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'job_type_id') {
            $model = JobType::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'option_id') {
            $model = Option::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'component_id') {
            $model = Component::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'product_type_id') {
            $model = ProductType::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'item_type_id') {
            $model = ItemType::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'size_id') {
            $model = Size::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'industry_id') {
            $model = Industry::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'account_term_id') {
            $model = AccountTerm::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'job_type_id') {
            $model = JobType::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if ($field == 'price_structure_id') {
            $model = PriceStructure::findOne($value);
            return $model ? $model->name . ' [' . $model->id . ']' : '{' . $value . '}';
        }
        if (in_array($field, ['staff_lead_id', 'staff_rep_id', 'staff_csr_id'])) {
            $model = \app\models\User::findOne($value);
            return $model ? $model->label . ' [' . $model->id . ']' : '{' . $value . '}';
        }

        return $value;
    }

    /**
     * @param $count
     * @param $total
     * @return string
     */
    public static function getProgressBarHtml($count, $total)
    {
        $progress = $total ? $count / $total * 100 : 100;
        return Html::tag('div', Html::tag('div', $count . '/' . $total, [
            'class' => 'progress-bar',
            'aria-valuenow' => $progress,
            'aria-valuemin' => 0,
            'aria-valuemax' => 100,
            'style' => 'width:' . $progress . '%',
        ]), ['class' => 'progress']);
    }

    /**
     * @param $icon
     * @param array $options
     * @return string
     */
    public static function getIcon($icon, $options = [])
    {
        return Html::img(Yii::$app->params['s3BucketUrl'] . '/img/icon/' . $icon, ArrayHelper::merge([
            'width' => 16,
            'height' => 16,
            'data-toggle' => 'tooltip',
        ], $options));
    }

    /**
     * @param Company $company
     * @param Job $job
     * @param string $attribute
     * @return string
     */
    public static function getCompanyQuoteTime($company, $job = null, $attribute = 'quote_at')
    {
        if (!$job) {
            $job = Job::find()
                ->notDeleted()
                ->andWhere(['company_id' => $company->id])
                ->andWhere($attribute . ' IS NOT NULL')
                ->orderBy([$attribute => SORT_ASC])
                ->one();
        }
        if ($job) {
            $days = ceil((strtotime($job->$attribute) - $company->created_at) / 60 / 60 / 24);
            if ($days <= 7)
                return '7 days';
            if ($days <= 14)
                return '14 days';
            if ($days <= 30)
                return '30 days';
            if ($days <= 60)
                return '60 days';
            if ($days <= 90)
                return '90 days';
        }
        return 'none';
    }

    /**
     * @param $date
     * @param int $days
     * @param bool $workdaysOnly
     * @param bool $endOnWorkday
     * @return bool|string
     */
    public static function getRelativeDate($date, $days = 0, $workdaysOnly = true, $endOnWorkday = true)
    {
        $holidays = [
            '*-01-01', // New Year's Day
            '*-01-26', // Australia Day
            '*-04-25', // ANZAC Day
            '*-12-25', // Christmas Day
            '*-12-26', // Boxing Day
        ];
        $weekend = ['Sun', 'Sat'];
        $time = new DateTime($date);
        $count = abs($days);
        $method = ($days < 0) ? 'sub' : 'add';
        for ($i = 0; $i < $count; $i++) {
            $time->$method(new DateInterval('P1D'));
            if ($workdaysOnly || ($endOnWorkday && $i + 1 == $count)) {
                $skipDay = false;
                if (in_array($time->format('D'), $weekend))
                    $skipDay = true;
                if (in_array($time->format('Y-m-d'), $holidays))
                    $skipDay = true;
                if (in_array($time->format('*-m-d'), $holidays))
                    $skipDay = true;
                if ($skipDay)
                    $i--;
            }
        }
        return $time->format('Y-m-d');
    }

    /**
     * @param AuditEntry $auditEntry
     * @return bool|string
     */
    public static function getAuditRequestUrl($auditEntry)
    {
        $data = ArrayHelper::getColumn($auditEntry->data, 'data');
        if (!isset($data['audit/request'])) {
            return Url::to([$auditEntry->route ?: '/'], 'https');
        }
        $request = $data['audit/request'];
        $route = $auditEntry->route ?: (!empty($request['route']) ? $request['route'] : '/');
        return Url::to(ArrayHelper::merge([$route], $request['GET']), 'https');
    }

    /**
     * @param \app\models\User $user
     * @param int $size
     * @return string
     */
    public static function getUserAvatar($user, $size = 16)
    {
        $html = $user ? $user->getAvatar($size) : \app\models\User::getSystemAvatar($size);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        return $xpath->evaluate("string(//img/@src)");
    }

    /**
     * @param $text
     * @param string $color
     * @return string
     */
    public static function getTextImage($text, $color = 'white')
    {
        $cacheKey = 'Helper.getTextImage.' . md5($text) . '.' . $color;
        $src = Yii::$app->cacheFile->get($cacheKey);
        if (!$src) {
            $size = 5;
            $text = $text . '  ';
            $image = imagecreatetruecolor(imagefontwidth($size) * strlen($text), 43);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            // black text on white bg
            if ($color == 'white') {
                imagefill($image, 0, 0, $white);
                imagestring($image, $size, 10, (43 - 15) / 2, $text, $black);
            } // white text on black bg
            else {
                imagefill($image, 0, 0, $black);
                imagestring($image, $size, 10, (43 - 15) / 2, $text, $white);
            }
            ob_start();
            imagepng($image);
            imagedestroy($image);
            $contents = ob_get_contents();
            ob_end_clean();
            $src = 'data:image/png;base64,' . base64_encode($contents);
            Yii::$app->cacheFile->set($cacheKey, $src);
        }
        return $src;
    }

    /**
     * @param $string
     * @return string
     */
    public static function stringToColor($string)
    {
        $darker = 1.3;
        $rgb = substr(dechex(crc32(str_repeat($string, 10) . md5($string))), 0, 6);
        list($R16, $G16, $B16) = str_split($rgb, 2);
        $R = sprintf("%02X", floor(hexdec($R16) / $darker));
        $G = sprintf("%02X", floor(hexdec($G16) / $darker));
        $B = sprintf("%02X", floor(hexdec($B16) / $darker));
        return '#' . $R . $G . $B;
    }

    /**
     * @param ActiveRecord|Address|Attachment|Note|Notification $model
     */
    public static function clearRelatedCache($model)
    {
        $cacheModels = [
            Company::className(),
            Contact::className(),
            Job::className(),
            Product::className(),
            Item::className(),
            Package::className(),
            Pickup::className(),
            User::className(),
        ];
        if (in_array($model->model_name, $cacheModels)) {
            /** @var ActiveRecord|CacheBehavior $relatedModel */
            $relatedModelName = $model->model_name;
            $relatedModel = $relatedModelName::findOne($model->model_id);
            if ($relatedModel && is_callable([$relatedModel, 'clearCache'])) {

                // clear model cache
                $relatedModel->clearCache();

                // clear down the tree
                if ($model->model_name == Job::className()) {
                    /** @var Job $relatedModel */
                    foreach ($relatedModel->products as $product) {
                        $product->clearCache();
                        foreach ($product->items as $item) {
                            $item->clearCache();
                        }
                    }
                } elseif ($model->model_name == Product::className()) {
                    /** @var Product $relatedModel */
                    foreach ($relatedModel->items as $item) {
                        $item->clearCache();
                    }
                }

            }
        }
    }

    /**
     * @param $filename
     * @return int
     */
    public static function countCsvRows($filename)
    {
        if (!file_exists($filename)) {
            return 0;
        }
        $count = 0;
        $fh = fopen($filename, "r");
        while (fgetcsv($fh, 1000, ",") !== false) {
            $count++;
        }
        fclose($fh);
        return $count;
    }

    /**
     * @param $url
     * @return array
     */
    public static function scrapeTable($url)
    {
        try {
            $contents = file_get_contents($url);
            $tidy = new \tidy;
            $tidy->parseString($contents);
            $tidy->cleanRepair();
            $dom = new \DOMDocument();
            $dom->loadHTML($tidy);
        } catch (\Exception $e) {
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $trs = $xpath->query("//tr");
        $rows = [];
        foreach ($trs as $tr) {
            $row = [];
            foreach ($xpath->query('td', $tr) as $tds) {
                $row[] = trim($tds->textContent);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * @return Transaction[]
     */
    public static function beginTransactions()
    {
        $transactions = [];
        $transactions['db'] = Yii::$app->db->beginTransaction();
        $transactions['dbAudit'] = Yii::$app->dbAudit->beginTransaction();
        $transactions['dbGoldoc'] = Yii::$app->dbGoldoc->beginTransaction();
        $transactions['dbData'] = Yii::$app->dbData->beginTransaction();
        return $transactions;
    }

    /**
     * @param Transaction[] $transactions
     */
    public static function commitTransactions($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->commit();
        }
    }

    /**
     * @param Transaction[] $transactions
     */
    public static function rollBackTransactions($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->rollBack();
        }
    }

}