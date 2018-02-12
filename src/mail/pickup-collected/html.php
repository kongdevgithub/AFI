<?php
/**
 * @var \yii\web\View $this
 * @var array $pickups
 */

use app\models\Package;
use app\models\Pickup;
use yii\helpers\Html;

$this->params['headerImage'] = Yii::$app->params['s3BucketUrl'] . '/img/mail/afi/pickup-collected-header.jpg';

$output = [];

foreach ($pickups as $pickupInfo) {
    /** @var Pickup $pickup */
    $pickup = $pickupInfo['pickup'];
    /** @var Package[] $packages */
    $packages = $pickupInfo['packages'];

    $row = '<h3>pickup-' . $pickup->id . ' has been despatched.</h3>';
    $carrier = [];
    if ($pickup->carrier) {
        $carrier[] = 'Carrier Name: ' . $pickup->carrier->name;
    }
    if ($pickup->carrier_ref) {
        if ($pickup->carrier && $pickup->carrier->tracking_url) {
            $carrier[] = 'Tracking Link: ' . $pickup->getTrackingLink();
        } else {
            $carrier[] = 'Tracking Number: ' . $pickup->carrier_ref;
        }
    }
    if ($carrier) {
        $row .= Html::tag('p', implode('<br>', $carrier));
    }

    foreach ($packages as $package) {
        $job = $package->getFirstJob();
        if ($job) {
            $row .= '<p><strong>' . $job->getTitle() . '</strong></p><br>';
        }
        $row .= $package->address->getLabel() . '<br>';
        $row .= 'package-' . $package->id;
    }
    $output[] = $row;
}

echo implode('<hr>', $output);
