<?php

namespace app\components;

use app\models\Address;
use app\models\Company;
use app\models\Contact;
use app\models\Feedback;
use app\models\Item;
use app\models\Job;
use app\models\Log;
use app\models\Package;
use app\models\Pickup;
use app\models\Product;
use bedezign\yii2\audit\Audit;
use Yii;
use yii\base\Component;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * EmailManager
 */
class EmailManager extends Component
{

    /**
     * @param Job $job
     * @param null $to
     * @return bool
     */
    public static function sendQuoteApproval($job, $to = null)
    {
        $bcc = [
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            $job->staffLead->email => $job->staffLead->label,
        ];
        if ($job->staffDesigner) {
            $bcc[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'quote-approval/html', 'text' => 'quote-approval/text'], [
                'job' => $job,
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to ?: [$job->contact->email => $job->contact->label])
            ->setBcc($bcc)
            ->setSubject(Yii::t('app', 'Your quote is ready!') . ' ' . $job->getTitle())
            ->attachContent(PdfManager::getJobQuote($job)->toString(), [
                'fileName' => $job->quote_template . '_' . Inflector::slug($job->name) . '_quote_' . $job->id . '.pdf',
                'contentType' => 'application/pdf',
            ])
            ->send();
    }

    /**
     * @param Job $job
     * @param null $to
     * @return bool
     */
    public static function sendArtworkApproval($job, $to = null)
    {
        $bcc = [
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            $job->staffLead->email => $job->staffLead->label,
            'lucia@afibranding.com.au' => 'Lucia Hanigan',
        ];
        if ($job->staffDesigner) {
            $bcc[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'artwork-approval/html', 'text' => 'artwork-approval/text'], [
                'job' => $job,
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to ?: [$job->contact->email => $job->contact->label])
            ->setBcc($bcc)
            ->setSubject(Yii::t('app', 'Your job requires artwork approval!') . ' ' . $job->getTitle())
            ->send();
    }

    /**
     * @param Job $job
     * @param null $to
     * @return bool
     */
    public static function sendJobInvoice($job, $to = null)
    {
        $bcc = [
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            $job->staffLead->email => $job->staffLead->label,
        ];
        if ($job->staffDesigner) {
            $bcc[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'job-invoice/html', 'text' => 'job-invoice/text'], [
                'job' => $job,
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to ?: [$job->contact->email => $job->contact->label])
            ->setBcc($bcc)
            ->setSubject(Yii::t('app', 'Your proforma invoice is ready!') . ' ' . $job->getTitle())
            ->attachContent(PdfManager::getJobInvoice($job)->toString(), [
                'fileName' => $job->quote_template . '_' . Inflector::slug($job->name) . '_invoice_' . $job->id . '.pdf',
                'contentType' => 'application/pdf',
            ])
            ->attachContent(file_get_contents('http://www.afibranding.com.au/pdfs/payment-details-form.pdf'), [
                'fileName' => 'payment-details-form.pdf',
                'contentType' => 'application/pdf',
            ])
            ->send();
    }

    /**
     * @param Pickup[] $pickups
     * @param null $to
     * @return bool
     */
    public static function sendPickupCollected($pickups, $to = null)
    {
        $messages = [];
        foreach ($pickups as $pickup) {
            foreach ($pickup->packages as $package) {
                foreach ($package->units as $unit) {
                    $job = $unit->item->product->job;
                    $_to = $to ?: [$job->contact->email => $job->contact->label];
                    if (!isset($messages[$job->contact_id])) {
                        $messages[$job->contact_id] = [
                            'to' => $_to,
                            'from' => [$job->staffRep->email => $job->staffRep->label],
                            'bcc' => [
                                //'james@afibranding.com.au',
                            ],
                            'jobs' => [],
                            'packages' => [],
                        ];
                    }
                    $messages[$job->contact_id]['bcc'][$job->staffRep->email] = $job->staffRep->label;
                    $messages[$job->contact_id]['bcc'][$job->staffCsr->email] = $job->staffCsr->label;
                    $messages[$job->contact_id]['bcc'][$job->staffLead->email] = $job->staffLead->label;
                    if ($job->staffDesigner) {
                        $messages[$job->contact_id]['bcc'][$job->staffDesigner->email] = $job->staffDesigner->label;
                    }
                    $messages[$job->contact_id]['jobs'][$job->id] = $job;
                    $messages[$job->contact_id]['pickups'][$pickup->id]['pickup'] = $pickup;
                    $messages[$job->contact_id]['pickups'][$pickup->id]['packages'][$package->id] = $package;
                }
            }
        }
        foreach ($messages as $message) {
            $jobs = [];
            foreach ($message['jobs'] as $job) {
                $jobs[] = '#' . $job->vid;
            }
            $mailer = Yii::$app->mailer
                ->compose(['html' => 'pickup-collected/html', 'text' => 'pickup-collected/text'], [
                    'pickups' => $message['pickups'],
                ])
                ->setFrom($message['from'])
                ->setTo($message['to'])
                ->setBcc($message['bcc'])
                ->setSubject('Your job has been despatched! ' . implode(', ', $jobs));
            foreach ($message['pickups'] as $pickupInfo) {
                foreach ($pickupInfo['packages'] as $package) {
                    /** @var Package $package */
                    $filename = static::tmpPath() . '/package-' . $package->id . '.pdf';
                    PdfManager::getPackage($package)->saveAs($filename);
                    $mailer->attach($filename);
                }
            }
            $mailer->send();
        }
        return true;
    }

    /**
     * @param Pickup $pickup
     * @param null $to
     * @return bool
     */
    public static function sendPickupDelivered($pickup, $to = null)
    {
        $messages = [];
        foreach ($pickup->packages as $package) {
            foreach ($package->units as $unit) {
                $job = $unit->item->product->job;
                $_to = $to ?: [$job->contact->email => $job->contact->label];
                if (!isset($messages[$job->contact_id])) {
                    $messages[$job->contact_id] = [
                        'to' => $_to,
                        'from' => [$job->staffRep->email => $job->staffRep->label],
                        'bcc' => [
                            //'james@afibranding.com.au',
                        ],
                        'jobs' => [],
                        'packages' => [],
                    ];
                }
                $messages[$job->contact_id]['bcc'][$job->staffRep->email] = $job->staffRep->label;
                $messages[$job->contact_id]['bcc'][$job->staffCsr->email] = $job->staffCsr->label;
                $messages[$job->contact_id]['bcc'][$job->staffLead->email] = $job->staffLead->label;
                if ($job->staffDesigner) {
                    $messages[$job->contact_id]['bcc'][$job->staffDesigner->email] = $job->staffDesigner->label;
                }
                $messages[$job->contact_id]['jobs'][$job->id] = $job;
                $messages[$job->contact_id]['pickups'][$pickup->id]['pickup'] = $pickup;
                $messages[$job->contact_id]['pickups'][$pickup->id]['packages'][$package->id] = $package;
            }
        }
        foreach ($messages as $message) {
            $jobs = [];
            foreach ($message['jobs'] as $job) {
                $jobs[] = '#' . $job->vid;
            }
            $mailer = Yii::$app->mailer
                ->compose(['html' => 'pickup-delivered/html', 'text' => 'pickup-delivered/text'], [
                    'pickups' => $message['pickups'],
                ])
                ->setFrom($message['from'])
                ->setTo($message['to'])
                ->setBcc($message['bcc'])
                ->setSubject('Your job has been delivered! ' . implode(', ', $jobs));
            $mailer->send();
        }
        return true;
    }

    /**
     * @param Job $job
     * @return bool
     */
    public static function sendJobPriceAlert($job)
    {
        return Yii::$app->mailer
            ->compose(['html' => 'job-price-alert/html', 'text' => 'job-price-alert/text'], [
                'job' => $job,
                'url' => Url::to(['//job/quote', 'id' => $job->id], 'https'),
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo([
                'glenn@afibranding.com.au',
                'brendon@afibranding.com.au',
                'gerard@afibranding.com.au',
                //'webmaster@afibranding.com.au',
            ])
            ->setSubject(Yii::t('app', 'A quote is within 30% of cost price!') . ' ' . $job->getTitle())
            ->send();
    }

    /**
     * @param Job $job
     * @return bool
     */
    public static function sendJobSuspendedAlert($job)
    {
        $to = [
            $job->staffLead->email => $job->staffLead->label,
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            'glenn@afibranding.com.au',
            'brendon@afibranding.com.au',
            'meagan@afibranding.com.au',
            'accounts@afibranding.com.au',
            //'webmaster@afibranding.com.au',
            'nick@afibranding.com.au',
            'alanna@afibranding.com.au',
            'dan@afibranding.com.au',
            //'ian@afibranding.com.au',
        ];
        if ($job->staffDesigner) {
            $to[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'job-suspended-alert/html', 'text' => 'job-suspended-alert/text'], [
                'job' => $job,
                'url' => Url::to(['//job/view', 'id' => $job->id], 'https'),
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to)
            ->setSubject(Yii::t('app', 'A job has been suspended!') . ' ' . $job->getTitle())
            ->send();
    }

    /**
     * @param Job $job
     * @return bool
     */
    public static function sendJobCancelledAlert($job)
    {
        $to = [
            $job->staffLead->email => $job->staffLead->label,
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            'glenn@afibranding.com.au',
            'brendon@afibranding.com.au',
            'meagan@afibranding.com.au',
            'accounts@afibranding.com.au',
            //'webmaster@afibranding.com.au',
            'nick@afibranding.com.au',
            'alanna@afibranding.com.au',
            'dan@afibranding.com.au',
            //'ian@afibranding.com.au',
        ];
        if ($job->staffDesigner) {
            $to[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'job-cancelled-alert/html', 'text' => 'job-cancelled-alert/text'], [
                'job' => $job,
                'url' => Url::to(['//job/view', 'id' => $job->id], 'https'),
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to)
            ->setSubject(Yii::t('app', 'A job has been cancelled!' . $job->getTitle()))
            ->send();
    }

    /**
     * @param Item $item
     * @return bool
     */
    public static function sendItemHoldAlert($item)
    {
        $job = $item->product->job;
        $to = [
            $job->staffLead->email => $job->staffLead->label,
            $job->staffRep->email => $job->staffRep->label,
            $job->staffCsr->email => $job->staffCsr->label,
            //'webmaster@afibranding.com.au',
            'nick@afibranding.com.au',
            'alanna@afibranding.com.au',
            'dan@afibranding.com.au',
            //'ian@afibranding.com.au',
        ];
        if ($job->staffDesigner) {
            $to[$job->staffDesigner->email] = $job->staffDesigner->label;
        }
        return Yii::$app->mailer
            ->compose(['html' => 'item-hold-alert/html', 'text' => 'item-hold-alert/text'], [
                'item' => $item,
                'url' => Url::to(['//item/view', 'id' => $item->id], 'https'),
            ])
            ->setFrom([$job->staffRep->email => $job->staffRep->label])
            ->setTo($to)
            ->setSubject(Yii::t('app', 'An item has been put on hold!' . $item->getTitle()))
            ->send();
    }

    /**
     * @param int $hub_spot_id
     * @param Company $company
     * @param array $data
     * @return bool
     */
    public static function sendHubSpotCompanyPullError($hub_spot_id, $company, $data)
    {
        $staffRep = \app\models\User::findOne($company->staff_rep_id);
        $to = [];
        if ($staffRep) {
            $from = [$staffRep->email => $staffRep->label];
            $to[$staffRep->email] = $staffRep->label;
        } else {
            $from = 'webmaster@afibranding.com.au';
        }
        $to[] = 'webmaster@afibranding.com.au';

        return Yii::$app->mailer
            ->compose(['html' => 'hub-spot-company-pull-error/html', 'text' => 'hub-spot-company-pull-error/text'], [
                'hub_spot_id' => $hub_spot_id,
                'company' => $company,
                'data' => $data,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('HubSpot Company Pull Error')
            ->send();
    }

    /**
     * @param int $hub_spot_id
     * @param Company $company
     * @param Address $address
     * @param array $data
     * @return bool
     */
    public static function sendHubSpotCompanyAddressPullError($hub_spot_id, $company, $address, $data)
    {
        $staffRep = \app\models\User::findOne($company->staff_rep_id);
        $to = [];
        if ($staffRep) {
            $from = [$staffRep->email => $staffRep->label];
            $to[$staffRep->email] = $staffRep->label;
        } else {
            $from = 'webmaster@afibranding.com.au';
        }
        $to[] = 'webmaster@afibranding.com.au';

        return Yii::$app->mailer
            ->compose(['html' => 'hub-spot-company-address-pull-error/html', 'text' => 'hub-spot-company-address-pull-error/text'], [
                'hub_spot_id' => $hub_spot_id,
                'company' => $company,
                'address' => $address,
                'data' => $data,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('HubSpot Company Address Pull Error')
            ->send();
    }

    /**
     * @param int $hub_spot_id
     * @param Contact $contact
     * @param array $data
     * @return bool
     */
    public static function sendHubSpotContactPullError($hub_spot_id, $contact, $data)
    {
        $staffRep = $contact->defaultCompany ? \app\models\User::findOne($contact->defaultCompany->staff_rep_id) : false;
        $to = [];
        if ($staffRep) {
            $from = [$staffRep->email => $staffRep->label];
            $to[$staffRep->email] = $staffRep->label;
        } else {
            $from = 'webmaster@afibranding.com.au';
        }
        $to[] = 'webmaster@afibranding.com.au';

        return Yii::$app->mailer
            ->compose(['html' => 'hub-spot-contact-pull-error/html', 'text' => 'hub-spot-contact-pull-error/text'], [
                'hub_spot_id' => $hub_spot_id,
                'contact' => $contact,
                'data' => $data,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('HubSpot Contact Pull Error')
            ->send();
    }

    /**
     * @param int $hub_spot_id
     * @param Job $job
     * @param array $data
     * @return bool
     */
    public static function sendHubSpotDealPullError($hub_spot_id, $job, $data)
    {
        $staffRep = \app\models\User::findOne($job->staff_rep_id);
        $to = [];
        if ($staffRep) {
            $from = [$staffRep->email => $staffRep->label];
            $to[$staffRep->email] = $staffRep->label;
        } else {
            $from = 'webmaster@afibranding.com.au';
        }
        $to[] = 'webmaster@afibranding.com.au';

        return Yii::$app->mailer
            ->compose(['html' => 'hub-spot-deal-pull-error/html', 'text' => 'hub-spot-deal-pull-error/text'], [
                'hub_spot_id' => $hub_spot_id,
                'job' => $job,
                'data' => $data,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('HubSpot Job Pull Error')
            ->send();
    }

    /**
     * @param Job $job
     * @param string $message
     * @return bool
     */
    public static function sendDearSalePushError($job, $message)
    {
        $to = 'tnuske@octanorm.com.au';
        $from = 'tnuske@octanorm.com.au';
        return Yii::$app->mailer
            ->compose(['html' => 'dear-sale-push-error/html', 'text' => 'dear-sale-push-error/text'], [
                'job' => $job,
                'message' => $message,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('Dear Sale Push Error')
            ->send();
    }

    /**
     * @param Component $component
     * @param string $message
     * @return bool
     */
    public static function sendDearProductPushError($component, $message)
    {
        $to = 'tnuske@octanorm.com.au';
        $from = 'tnuske@octanorm.com.au';
        return Yii::$app->mailer
            ->compose(['html' => 'dear-product-push-error/html', 'text' => 'dear-product-push-error/text'], [
                'component' => $component,
                'message' => $message,
            ])
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('Dear Product Push Error')
            ->send();
    }

    /**
     * @param Feedback $feedback
     * @param null $to
     * @return bool
     */
    public static function sendFeedbackSurvey($feedback, $to = null)
    {
        return Yii::$app->mailer
            ->compose(['html' => 'feedback-survey/html', 'text' => 'feedback-survey/text'], [
                'feedback' => $feedback,
            ])
            ->setFrom(['brendon@afibranding.com.au' => 'Brendon Rowse'])
            ->setTo($to ?: [$feedback->contact->email => $feedback->contact->label])
            ->setSubject(Yii::t('app', 'Let us know how we are going'))
            ->send();
    }

    /**
     * @param Contact $contact
     * @return bool
     */
    public static function sendFeedbackUnsubscribeAlert($contact)
    {
        if (!$contact->defaultCompany) {
            return false;
        }
        $rep = $contact->defaultCompany->staffRep;
        return Yii::$app->mailer
            ->compose(['html' => 'feedback-unsubscribe-alert/html', 'text' => 'feedback-unsubscribe-alert/text'], [
                'contact' => $contact,
            ])
            ->setFrom([$rep->email => $rep->label])
            ->setTo([$rep->email => $rep->label])
            ->setSubject(Yii::t('app', 'Contact has unsubscribed from feedback'))
            ->send();
    }

    /**
     * @param $to
     * @param Job $job
     * @param Log $log
     * @return bool
     */
    public static function sendJobChangedAlert($to, $job, $log)
    {
        $user = Yii::$app->user->identity;
        return Yii::$app->mailer
            ->compose(['html' => 'job-changed-alert/html', 'text' => 'job-changed-alert/text'], [
                'job' => $job,
                'log' => $log,
                'url' => Url::to(['//job/view', 'id' => $job->id], 'https'),
            ])
            ->setFrom([$user->email => $user->label])
            ->setTo($to)
            //->setCc(['webmaster@afibranding.com.au'])
            ->setSubject(Yii::t('app', 'A job has been changed!') . ' ' . $job->getTitle())
            ->send();
    }

    /**
     * @param $to
     * @param Product $product
     * @param Log $log
     * @return bool
     */
    public static function sendProductUpdatedAlert($to, $product, $log)
    {
        $user = Yii::$app->user->identity;
        return Yii::$app->mailer
            ->compose(['html' => 'product-updated-alert/html', 'text' => 'product-updated-alert/text'], [
                'product' => $product,
                'log' => $log,
                'url' => Url::to(['//product/view', 'id' => $product->id], 'https'),
            ])
            ->setFrom([$user->email => $user->label])
            ->setTo($to)
            //->setCc(['webmaster@afibranding.com.au'])
            ->setSubject(Yii::t('app', 'A product has been updated!') . ' ' . $product->getTitle())
            ->send();
    }

    /**
     * @param $to
     * @param Product $product
     * @param Log $log
     * @return bool
     */
    public static function sendProductCreatedAlert($to, $product, $log)
    {
        $user = Yii::$app->user->identity;
        return Yii::$app->mailer
            ->compose(['html' => 'product-created-alert/html', 'text' => 'product-created-alert/text'], [
                'product' => $product,
                'log' => $log,
                'url' => Url::to(['//product/view', 'id' => $product->id], 'https'),
            ])
            ->setFrom([$user->email => $user->label])
            ->setTo($to)
            //->setCc(['webmaster@afibranding.com.au'])
            ->setSubject(Yii::t('app', 'A product has been created!') . ' ' . $product->getTitle())
            ->send();
    }

    /**
     * @param $to
     * @param Product $product
     * @param Log $log
     * @return bool
     */
    public static function sendProductDeletedAlert($to, $product, $log)
    {
        $user = Yii::$app->user->identity;
        return Yii::$app->mailer
            ->compose(['html' => 'product-deleted-alert/html', 'text' => 'product-deleted-alert/text'], [
                'product' => $product,
                'log' => $log,
                'url' => Url::to(['//product/view', 'id' => $product->id], 'https'),
            ])
            ->setFrom([$user->email => $user->label])
            ->setTo($to)
            //->setCc(['webmaster@afibranding.com.au'])
            ->setSubject(Yii::t('app', 'A product has been deleted!') . ' ' . $product->getTitle())
            ->send();
    }

    /**
     * @param Job $job
     * @return bool
     */
    public static function sendClientQuoteAlert($job)
    {
        return Yii::$app->mailer
            ->compose(['html' => 'client-quote-alert/html', 'text' => 'client-quote-alert/text'], [
                'job' => $job,
                'url' => Url::to(['//job/quote', 'id' => $job->id], 'https'),
            ])
            ->setFrom([$job->staffCsr->email => $job->staffCsr->label])
            ->setTo([$job->staffRep->email => $job->staffRep->label])
            ->setSubject(Yii::t('app', 'A quote has been created by a client!') . ' ' . $job->getTitle())
            ->send();
    }

    /**
     * @return bool
     */
    public static function sendDatabaseTransactionOpenAlert()
    {
        return Yii::$app->mailer->compose()
            ->setFrom(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setTo(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setSubject('transaction was found open')
            ->setTextBody('audit-' . Audit::getInstance()->getEntry()->id)
            //->setHtmlBody($message['html'])
            ->send();
    }

    /**
     * @param Job $job
     * @return bool
     */
    public static function sendQuoteTotalsCheckAlert($job)
    {
        $productTotal = 0;
        foreach ($job->products as $product) {
            $productTotal += $product->quote_factor_price - $product->quote_discount_price;
        }
        return Yii::$app->mailer->compose()
            ->setFrom(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setTo(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setSubject('job has incorrect totals')
            ->setTextBody(implode("\n", [
                'job-' . $job->id,
                'job wholesale: ' . number_format($job->quote_wholesale_price, 2),
                'product total: ' . number_format($productTotal, 2),
            ]))
            //->setHtmlBody($message['html'])
            ->send();
    }

    /**
     * @param $csv
     * @return bool
     */
    public static function sendGoldocAfiExport($csv)
    {
        return Yii::$app->mailer->compose()
            ->setFrom(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setTo(['nick@afibranding.com.au' => 'Nick Bolitho'])
            ->setSubject('GOLDOC AFI Daily Import - ' . date('Y-m-d'))
            ->setTextBody(implode("\n", [
                'Please find export CSV attached.',
            ]))
            ->attachContent(YdCsv::arrayToCsv($csv), [
                'fileName' => 'goldoc-afi-export-' . date('Y-m-d-H-i-s') . '.csv',
                'contentType' => 'text/csv',
            ])
            ->send();
    }

    /**
     * @param $csv
     * @return bool
     */
    public static function sendGoldocAdgExport($csv)
    {
        $mailer = Yii::$app->mailer->compose()
            ->setFrom(['webmaster@afibranding.com.au' => Yii::$app->name])
            ->setTo(['vickyb@activedisplay.com.au' => 'Vicky Bouphassavanh'])
            ->setCc([
                'webmaster@afibranding.com.au' => Yii::$app->name,
                'nick@afibranding.com.au' => 'Nick Bolitho',
            ])
            ->setSubject('GOLDOC ADG Daily Import - ' . date('Y-m-d'));
        if ($csv) {
            $mailer->setTextBody('Please find export CSV attached.')
                ->attachContent(YdCsv::arrayToCsv($csv), [
                    'fileName' => 'goldoc-adg-export-' . date('Y-m-d-H-i-s') . '.csv',
                    'contentType' => 'text/csv',
                ]);
        } else {
            $mailer->setTextBody('No products today...');
        }
        return $mailer->send();
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    private static function tmpPath()
    {
        $path = Yii::$app->runtimePath . '/email-manager';
        FileHelper::createDirectory($path);
        return $path;
    }
}