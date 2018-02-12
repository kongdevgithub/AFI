<?php

namespace app\models;

use app\components\Helper;
use app\models\query\HubSpotDealQuery;
use SevenShores\Hubspot\Exceptions\BadRequest;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "hub_spot".
 */
class HubSpotDeal extends HubSpot
{

    /**
     * TODO change to app\models\Job
     */
    const MODEL_NAME = 'app\models\Deal';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->model_name = self::MODEL_NAME;
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->model_name = self::MODEL_NAME;
        return parent::beforeSave($insert);
    }

    /**
     * @return HubSpotDealQuery
     */
    public static function find()
    {
        return new HubSpotDealQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
    }

    /**
     * @param $model_id
     * @return \SevenShores\Hubspot\Http\Response|bool
     * @throws BadRequest
     * @throws Exception
     */
    public static function hubSpotPush($model_id)
    {
        if (YII_ENV != 'prod') return false;

        $hubSpotDeal = self::find()->andWhere(['model_id' => $model_id])->one();
        if (!$hubSpotDeal) {
            $hubSpotDeal = new HubSpotDeal();
            $hubSpotDeal->model_id = $model_id;
        }

        $hubSpotApi = Yii::$app->hubSpotApi;
        $job = Job::findOne($hubSpotDeal->model_id);
        if (!$job) {
            return false;
        }

        // log push
        Log::log('hubspot deal push', $job);

        // delete
        if ($job->deleted_at) {
            $response = $hubSpotApi->client->deals()->delete($hubSpotDeal->hub_spot_id);
            $response = $hubSpotApi->cleanResponseData($response->getData());
        } else {
            $data = [];
            $data['console_url'] = Url::to(['/job/quote', 'id' => $job->id], 'https');

            // map data
            $data['dealname'] = $job->name;
            $data['amount'] = $job->quote_total_price;
            $data['win_chance'] = $job->quote_win_chance;

            // map status
            if ($job->status == 'job/draft') {
            } elseif ($job->status == 'job/quote') {
                $data['dealstage'] = 'contractsent';
            } elseif ($job->status == 'job/quoteLost') {
                $data['dealstage'] = 'closedlost';
                $data['closed_lost_reason'] = $job->quote_lost_reason;
                if ($job->complete_at) {
                    $data['closedate'] = $job->quote_lost_at * 1000;
                }
            } else {
                $data['dealstage'] = 'closedwon';
                $data['closed_lost_reason'] = '';
                if ($job->production_pending_at) {
                    $data['closedate'] = $job->production_pending_at * 1000;
                }
            }

            // map owner
            $hubSpotUser = HubSpotUser::findOne(['model_id' => $job->staff_rep_id]);
            if ($hubSpotUser) {
                $data['hubspot_owner_id'] = $hubSpotUser->hub_spot_id;
            }

            // map company/contact
            $hubSpotCompany = HubSpotCompany::findOne(['model_id' => $job->company_id]);
            $hubSpotContact = HubSpotContact::findOne(['model_id' => $job->contact_id]);

            // create/update
            if (!$hubSpotDeal->hub_spot_id) {
                $response = $hubSpotApi->client->deals()->create([
                    'properties' => $hubSpotApi->cleanRequestData($data),
                    'associations' => [
                        'associatedCompanyIds' => $hubSpotCompany ? [$hubSpotCompany->hub_spot_id] : [],
                        'associatedVids' => $hubSpotContact ? [$hubSpotContact->hub_spot_id] : [],
                    ],
                ]);
                $response = $hubSpotApi->cleanResponseData($response->getData());
                $hubSpotDeal->hub_spot_id = (string)$response['dealId'];
            } else {
                $response = $hubSpotApi->client->deals()->update($hubSpotDeal->hub_spot_id, ['properties' => $hubSpotApi->cleanRequestData($data)]);

                // contacts
                $oldContacts = isset($response->data->associations->associatedVids) ? $response->data->associations->associatedVids : [];
                foreach ($oldContacts as $k => $oldContact) {
                    if ($hubSpotContact && $hubSpotContact->hub_spot_id == $oldContact) unset($oldContacts[$k]);
                }
                if (!empty($oldContacts)) {
                    $hubSpotApi->client->deals()->disassociateFromContact($hubSpotDeal->hub_spot_id, $oldContacts);
                }
                if ($hubSpotContact) {
                    $hubSpotApi->client->deals()->associateWithContact($hubSpotDeal->hub_spot_id, [$hubSpotContact->hub_spot_id]);
                }

                // companies
                $oldCompanies = isset($response->data->associations->associatedCompanyIds) ? $response->data->associations->associatedCompanyIds : [];
                foreach ($oldCompanies as $k => $oldCompany) {
                    if ($hubSpotCompany && $hubSpotCompany->hub_spot_id == $oldCompany) unset($oldCompanies[$k]);
                }
                if (!empty($oldCompanies)) {
                    $hubSpotApi->client->deals()->disassociateFromCompany($hubSpotDeal->hub_spot_id, $oldCompanies);
                }
                if ($hubSpotCompany) {
                    $hubSpotApi->client->deals()->associateWithCompany($hubSpotDeal->hub_spot_id, [$hubSpotCompany->hub_spot_id]);
                }
            }
        }

        // save
        $hubSpotDeal->hub_spot_pushed = time();
        if (!$hubSpotDeal->save()) {
            throw new Exception('cannot save HubSpotDeal [model_id:' . $hubSpotDeal->model_id . '] ' . Helper::getErrorString($hubSpotDeal));
        }

        return $response;
    }

    /**
     * @param $hub_spot_id
     * @param $data
     * @param null $receivedTime
     * @return bool|array
     * @throws Exception
     */
    public static function hubSpotPull($hub_spot_id, $data = null, $receivedTime = null)
    {
        if (YII_ENV != 'prod') return false;

        $hubSpotApi = Yii::$app->hubSpotApi;

        $hubSpotDeal = self::find()->andWhere(['hub_spot_id' => $hub_spot_id])->one();
        if (!$hubSpotDeal) {
            $hubSpotDeal = new HubSpotDeal();
            $hubSpotDeal->hub_spot_id = $hub_spot_id;
        } else {
            if ($receivedTime && $hubSpotDeal->hub_spot_pulled > $receivedTime) {
                return false;
            }
        }

        if ($data === null) {
            $data = $hubSpotApi->cleanResponseData($hubSpotApi->client->deals()->getById($hub_spot_id)->getData());
        }
        if (empty($data['associations']['associatedVids']) || empty($data['associations']['associatedCompanyIds'])) {
            return false;
        }

        // begin transaction
        $transaction = Yii::$app->dbData->beginTransaction();

        // find company
        $hubSpotCompany = false;
        if (!empty($data['associations']['associatedCompanyIds'])) {
            $hubSpotCompany = HubSpotCompany::findOne(['hub_spot_id' => $data['associations']['associatedCompanyIds'][0]]);
        }

        // load job
        $job = $hubSpotDeal->model_id ? Job::findOne($hubSpotDeal->model_id) : false;
        if (!$job) {
            $job = new Job();
            if ($hubSpotCompany) {
                $job->company_id = $hubSpotCompany->model_id;
            }
            $job->loadDefaultValues();
        }
        $newJob = $job->isNewRecord;

        // map fields
        $job->name = isset($data['properties']['dealname']) ? $data['properties']['dealname'] : null;
        $job->due_date || $job->due_date = date('Y-m-d', !empty($data['properties']['closedate']) ? $data['properties']['closedate'] / 1000 : strtotime('+7days'));
        $job->quote_win_chance = isset($data['properties']['win_chance']) ? $data['properties']['win_chance'] : 50;

        // map status
        if (isset($data['properties']['dealstage'])) {
            if (in_array($job->status, ['job/draft', 'job/quote', 'job/quoteLost', 'job/production'])) {
                if ($data['properties']['dealstage'] == 'contractsent') {
                    $job->status = 'job/quote';
                } elseif ($data['properties']['dealstage'] == 'closedlost') {
                    $job->status = 'job/quoteLost';
                    $job->quote_lost_reason = $data['properties']['closed_lost_reason'];
                } elseif ($data['properties']['dealstage'] == 'closedwon') {
                    $job->status = 'job/production';
                }
            }
        }

        // map contact
        if (!empty($data['associations']['associatedVids'])) {
            $hubSpotContact = HubSpotContact::findOne(['hub_spot_id' => $data['associations']['associatedVids'][0]]);
            if ($hubSpotContact) {
                $job->contact_id = $hubSpotContact->model_id;
            }
        }

        // map staff
        if (!empty($data['properties']['hubspot_owner_id'])) {
            $hubSpotUser = HubSpotUser::findOne(['hub_spot_id' => $data['properties']['hubspot_owner_id']]);
            if ($hubSpotUser) {
                $job->staff_rep_id || $job->staff_rep_id = $hubSpotUser->model_id;
                $job->staff_csr_id || $job->staff_csr_id = $hubSpotUser->model_id;
            }
        }

        // save job
        //$job->hubSpotUpdate = false;
        if (!$job->save()) {
            $transaction->rollBack();
            throw new Exception('cannot save Job [id:' . $job->id . '|hub_spot_id:' . $hub_spot_id . '] ' . Helper::getErrorString($job));
        }

        // save HubSpotDeal
        if (!$hubSpotDeal->model_id) {
            $hubSpotDeal->model_id = $job->id;
        }
        $hubSpotDeal->hub_spot_pulled = time();
        if (!$hubSpotDeal->save()) {
            $transaction->rollBack();
            throw new Exception('cannot save HubSpotDeal [model_id:' . $hubSpotDeal->model_id . '] ' . Helper::getErrorString($hubSpotDeal));
        }

        // we did it!
        $transaction->commit();

        // log pull
        Log::log('hubspot deal pull', $job);

        // update console_url in hubspot
        if ($newJob) {
            $hubSpotApi->client->deals()->update($hubSpotDeal->hub_spot_id, [
                'properties' => $hubSpotApi->cleanRequestData([
                    'console_url' => Url::to(['/job/quote', 'id' => $job->id], 'https'),
                ]),
            ]);
        }

        return $data;
    }

}
