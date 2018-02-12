<?php

namespace app\modules\goldoc\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\linkbehavior\LinkBehavior;
use Yii;

/**
 * This is the model class for table "signage_fa".
 *
 * @mixin LinkBehavior
 */
class SignageFa extends base\SignageFa
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => LinkBehavior::className(),
            'moduleName' => 'goldoc',
        ];
        $behaviors[] = AuditTrailBehavior::className();
        return $behaviors;
    }

    /**
     * @return string
     */
    public function getVenueQuantities()
    {
        $venueQuantities = [];
        foreach ($this->signageFaToVenues as $signageFaToVenue) {
            $venueQuantities[] = $signageFaToVenue->venue->code . 'x' . $signageFaToVenue->quantity;
        }
        return implode(', ', $venueQuantities);
    }
}
