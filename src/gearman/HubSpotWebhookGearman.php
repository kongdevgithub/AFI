<?php

namespace app\gearman;

use app\models\Company;
use app\models\Contact;
use app\models\HubSpotCompany;
use app\models\HubSpotContact;
use app\models\HubSpotDeal;
use app\models\Job;
use app\models\Log;
use Yii;

/**
 * HubSpotWebhookGearman
 */
class HubSpotWebhookGearman extends BaseGearman
{

    /**
     * @inheritdoc
     */
    public function initParams($params)
    {

    }

    /**
     * @inheritdoc
     */
    public function executeWorkload($params)
    {
        if (!$params || empty($params['data'])) {
            Log::log('HubSpot Webhook - aborting - no data!');
            echo 'no data';
            return;
        }

        foreach ($params['data'] as $data) {
            $type = $data['subscriptionType'];
            $id = $data['objectId'];

            $mutexKey = 'HubSpotWebhookGearman.' . $type . '.' . $id;

            echo $mutexKey;
            //Log::log($mutexKey);

            if ($data['appId'] != 38742 || $data['portalId'] != 2659477) {
                Log::log($mutexKey . ' - BAD APP/PORTAL');
                continue;
            }
            //print_r($data);

            while (!Yii::$app->mutex->acquire($mutexKey)) {
                echo 'no lock - sleeping...';
                Log::log($mutexKey . ' - no lock - sleeping...');
                sleep(1);
            }

            // company
            if (in_array($type, ['company.create', 'company.propertyChange'])) {
                try {
                    $data = HubSpotCompany::hubSpotPull($id, null, $params['received_time']);
                    if ($data) {
                        echo ' - pulled company';
                        Log::log($mutexKey . ' - pulled company');
                    }
                    $_POST[$type][$id][] = $data;
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }
            if ($type == 'company.deletion') {
                try {
                    $hubSpotCompany = HubSpotCompany::findOne(['hub_spot_id' => $id]);
                    if ($hubSpotCompany) {
                        $company = Company::findOne($hubSpotCompany->model_id);
                        if ($company) {
                            $company->delete();
                            echo ' - deleted company-' . $company->id;
                            Log::log($mutexKey . ' - deleted company' . $company->id);
                        } else {
                            echo ' - no company';
                            Log::log($mutexKey . ' - no company');
                        }
                    } else {
                        echo ' - no hubspot company';
                        Log::log($mutexKey . ' - no hubspot company');
                    }
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }

            // contact
            if (in_array($type, ['contact.create', 'contact.propertyChange'])) {
                try {
                    $data = HubSpotContact::hubSpotPull($id, null, $params['received_time']);
                    if ($data) {
                        echo ' - pulled contact';
                        Log::log($mutexKey . ' - pulled contact');
                    }
                    $_POST[$type][$id][] = $data;
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }
            if ($type == 'contact.deletion') {
                try {
                    $hubSpotContact = HubSpotContact::findOne(['hub_spot_id' => $id]);
                    if ($hubSpotContact) {
                        $contact = Contact::findOne($hubSpotContact->model_id);
                        if ($contact) {
                            $contact->delete();
                            echo ' - deleted contact-' . $contact->id;
                            Log::log($mutexKey . ' - deleted contact' . $contact->id);
                        } else {
                            echo ' - no contact';
                            Log::log($mutexKey . ' - no contact');
                        }
                    } else {
                        echo ' - no hubspot contact';
                        Log::log($mutexKey . ' - no hubspot contact');
                    }
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }

            // deal
            if (in_array($type, ['deal.create', 'deal.propertyChange'])) {
                try {
                    $data = HubSpotDeal::hubSpotPull($id, null, $params['received_time']);
                    if ($data) {
                        echo ' - pulled deal';
                        Log::log($mutexKey . ' - pulled deal');
                    }
                    $_POST[$type][$id][] = $data;
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }
            if ($type == 'deal.deletion') {
                try {
                    $hubSpotDeal = HubSpotDeal::findOne(['hub_spot_id' => $id]);
                    if ($hubSpotDeal) {
                        $job = Job::findOne($hubSpotDeal->model_id);
                        if ($job) {
                            $job->delete();
                            echo ' - deleted job-' . $job->id;
                            Log::log($mutexKey . ' - deleted job' . $job->id);
                        } else {
                            echo ' - no job';
                            Log::log($mutexKey . ' - no job');
                        }
                    } else {
                        echo ' - no hubspot deal';
                        Log::log($mutexKey . ' - no hubspot deal');
                    }
                } catch (\Exception $e) {
                    echo ' - error';
                    Log::log($mutexKey . ' - error');
                }
            }

            // release lock
            Yii::$app->mutex->release($mutexKey);

        }

    }

}