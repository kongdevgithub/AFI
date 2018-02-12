<?php

namespace app\models;

use app\components\behaviors\WorkflowBehavior;
use app\components\EmailManager;
use app\components\Helper;
use app\components\Xml2Array;
use app\components\YdArray2Xml;
use app\models\workflow\PickupWorkflow;
use bedezign\yii2\audit\AuditTrailBehavior;
use bedezign\yii2\audit\models\AuditTrail;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "pickup".
 *
 * @mixin LinkBehavior
 * @mixin WorkflowBehavior
 * @mixin CacheBehavior
 */
class Pickup extends base\Pickup
{
    /**
     * @var bool
     */
    public $send_email = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->isNewRecord) {
            $this->enterWorkflow();
        }
        $this->on('EVENT_BEFORE_CHANGE_STATUS', [PickupWorkflow::className(), 'beforeChangeStatus']);
        $this->on('EVENT_AFTER_CHANGE_STATUS', [PickupWorkflow::className(), 'afterChangeStatus']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => WorkflowBehavior::className(),
            'defaultWorkflowId' => 'pickup',
            'propagateErrorsToModel' => true,
        ];
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['status'] = ['status', 'carrier_id', 'carrier_ref'];
        return $scenarios;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(Package::className(), ['pickup_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->inverseOf('pickup');
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // set the dates
        if ($insert || $this->isAttributeChanged('status')) {
            $date = time();
            if ($this->status == 'pickup/packing') {
                $this->complete_at = null;
                $this->collected_at = null;
                $this->emailed_at = null;
            }
            if ($this->status == 'pickup/complete') {
                if (!$this->complete_at)
                    $this->complete_at = $date;
            }
            if ($this->status == 'pickup/collected') {
                if (!$this->complete_at)
                    $this->complete_at = $date;
                if (!$this->collected_at)
                    $this->collected_at = $date;
                // send email
                if (!$this->emailed_at && $this->send_email) {
                    EmailManager::sendPickupCollected([$this]);
                    $this->emailed_at = time();
                }
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        foreach ($this->packages as $package) {
            $package->status = 'package/packing';
            $package->pickup_id = null;
            if (!$package->save()) {
                throw new Exception('Cannot save package-' . $package->id . ': ' . Helper::getErrorString($package));
            }
        }
        return true;
    }

    /**
     * @param bool $showInactiveMain
     * @return string
     */
    public function getStatusButtons($showInactiveMain = false)
    {
        return $this->getStatusButton();
    }

    /**
     * @return Job[]
     */
    public function getJobs()
    {
        $jobs = [];
        foreach ($this->packages as $package) {
            foreach ($package->units as $unit) {
                $job = $unit->item->product->job;
                if (!isset($jobs[$job->id])) {
                    $jobs[$job->id] = $job;
                }
            }
        }
        return $jobs;
    }


    /**
     * @return Job|bool
     */
    public function getFirstJob()
    {
        foreach ($this->packages as $package) {
            foreach ($package->units as $unit) {
                return $unit->item->product->job;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getLinkText()
    {
        return 'pickup-' . $this->id;
    }

    /**
     * @return string
     */
    public function getTrackingLink()
    {
        if ($this->carrier_ref) {
            $url = $this->getTrackingUrl();
            if ($url) {
                return Html::a($this->carrier_ref, $url, ['target' => '_blank']);
            }
            return $this->carrier_ref;
        }
        return '';
    }

    /**
     * @return string
     */
    public function getTrackingUrl()
    {
        if (!$this->carrier_ref || !$this->carrier || !$this->carrier->tracking_url) {
            return '';
        }
        $url = str_replace('__ID__', $this->carrier_ref, $this->carrier->tracking_url);
        if (strpos($this->carrier->tracking_url, 'tntexpress.com.au') !== false) {
            if (substr($this->carrier_ref, 0, 3) != 'AFI') {
                $url = str_replace('User=tntcct&Password=ccttnt', 'User=myfreight&Password=mYfr31ght', $url);
            }
        }
        return $url;
    }

    /**
     * @return query\LogQuery
     */
    public function getLogs()
    {
        $relation = Log::find();
        $relation->orOnCondition([
            'log.model_id' => $this->id,
            'log.model_name' => $this->className(),
        ]);
        $relation->orOnCondition([
            'log.model_id' => ArrayHelper::map($this->getPackages()->where('1=1')->all(), 'id', 'id'),
            'log.model_name' => Package::className(),
        ]);
        $relation->orderBy(['created_at' => SORT_DESC, 'audit_entry_id' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @param array $relations
     * @return \yii\db\ActiveQuery
     */
    public function getAuditTrails($relations = [])
    {
        $relation = AuditTrail::find();
        $relation->orOnCondition([
            'audit_trail.model_id' => $this->id,
            'audit_trail.model' => $this->className(),
        ]);
        if (in_array(Package::className(), $relations)) {
            /** @var Package[] $packages */
            $packages = $this->getPackages()->where('1=1')->all();
            $relation->orOnCondition([
                'audit_trail.model_id' => ArrayHelper::map($packages, 'id', 'id'),
                'audit_trail.model' => Package::className(),
            ]);
            if (in_array(Unit::className(), $relations)) {
                foreach ($packages as $package) {
                    /** @var Unit[] $units */
                    $units = $package->getUnits()->where('1=1')->all();
                    $relation->orOnCondition([
                        'audit_trail.model_id' => ArrayHelper::map($units, 'id', 'id'),
                        'audit_trail.model' => Unit::className(),
                    ]);
                }
            }
            if (in_array(Address::className(), $relations)) {
                foreach ($packages as $package) {
                    if ($package->address) {
                        $relation->orOnCondition([
                            'audit_trail.model_id' => $package->address->id,
                            'audit_trail.model' => Address::className(),
                        ]);
                    }
                }
            }
        }
        $relation->from([new Expression('{{%audit_trail}} USE INDEX (idx_audit_trail_field)')]);
        $relation->orderBy(['created' => SORT_DESC, 'id' => SORT_DESC]);
        $relation->multiple = true;
        return $relation;
    }

    /**
     * @return bool|string
     */
    public function scrapePod()
    {
        $url = $this->getTrackingUrl();
        if (!$url) {
            return false;
        }
        if (strpos($url, 'tracking.cope.com.au') !== false) {
            return $this->scrapePodCope();
        }
        if (strpos($url, 'online.toll.com.au') !== false) {
            return $this->scrapePodToll();
        }
        if (strpos($url, 'tntexpress.com.au') !== false) {
            return $this->scrapePodTnt();
        }
        return false;
    }

    public function scrapePodCope()
    {
        $url = 'http://tracking.cope.com.au/track.php?consignment=' . $this->carrier_ref;
        //if (strpos(file_get_contents($url), 'Current status: <b>Proof of Delivery</b>') !== false) {
        //    // eg view-source:http://tracking.cope.com.au/track.php?consignment=AFI004521
        //    // where to get the date?
        //}
        $rows = Helper::scrapeTable($url);
        $podStrings = [
            'proof of delivery',
            'we\'ve delivered your shipment',
            'sog proof of delivery',
            'proof of delivery image',
        ];
        foreach ($rows as $row) {
            if (isset($row[2])) {
                $potentialPodString = $row[2];
                $potentialPodString = trim(preg_replace('/\s+/', ' ', $potentialPodString));
                if (in_array(strtolower($potentialPodString), $podStrings)) {
                    return str_replace('/', '-', $row[0]);
                }
            }
        }
        return false;
    }

    public function scrapePodTnt()
    {
        $url = 'https://www.tntexpress.com.au/cct/TrackResultsCon.asp?User=tntcct&Password=ccttnt&con=' . $this->carrier_ref;
        if (substr($this->carrier_ref, 0, 3) != 'AFI') {
            $url = str_replace('User=tntcct&Password=ccttnt', 'User=myfreight&Password=mYfr31ght', $url);
        }
        $rows = Helper::scrapeTable($url);
        $podStrings = [
            'proof of delivery',
            'we\'ve delivered your shipment',
            'sog proof of delivery',
            'proof of delivery image',
        ];
        foreach ($rows as $row) {
            if (isset($row[3])) {
                $potentialPodString = $row[0];
                $potentialPodString = trim(preg_replace('/\s+/', ' ', $potentialPodString));
                if (in_array(strtolower($potentialPodString), $podStrings)) {
                    return str_replace('/', '-', $row[1] . ' ' . $row[2]);
                }
            }
        }
        return false;
    }

    public function scrapePodToll()
    {
        $url = 'https://api.trackingmore.com/v2/trackings/realtime';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Trackingmore-Api-Key: 54f82a68-4aa1-487c-9e6c-4aeeec4de70a',
            'Content-Type: application/json',
        ]);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, Json::encode([
            'carrier_code' => 'toll-ipec',
            'tracking_number' => trim($this->carrier_ref),
        ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        curl_close($curl);
        $response = Json::decode(trim($content));

        if (!empty($response['data']['items'][0]['origin_info']['trackinfo'])) {
            foreach ($response['data']['items'][0]['origin_info']['trackinfo'] as $trackInfo) {
                if ($trackInfo['StatusDescription'] == 'Successful Delivery') {
                    return $trackInfo['Date'];
                }
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['pod_date'] = Yii::t('app', 'POD Date');
        return $attributeLabels;
    }


}
