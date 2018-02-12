<?php

namespace app\models;

use app\components\EmailManager;
use app\components\Helper;
use app\models\query\HubSpotCompanyQuery;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;

/**
 * This is the model class for table "hub_spot".
 */
class HubSpotCompany extends HubSpot
{

    /**
     *
     */
    const MODEL_NAME = 'app\models\Company';

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
     * @return HubSpotCompanyQuery
     */
    public static function find()
    {
        return new HubSpotCompanyQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
    }

    /**
     * @param $model_id
     * @return \SevenShores\Hubspot\Http\Response|bool
     * @throws Exception
     */
    public static function hubSpotPush($model_id)
    {
        if (YII_ENV != 'prod') return false;

        $hubSpotCompany = self::find()->andWhere(['model_id' => $model_id])->one();
        if (!$hubSpotCompany) {
            $hubSpotCompany = new HubSpotCompany();
            $hubSpotCompany->model_id = $model_id;
        }

        $hubSpotApi = Yii::$app->hubSpotApi;

        $company = Company::findOne($hubSpotCompany->model_id);

        // log push
        Log::log('hubspot company push', $company);

        // delete
        if ($company->deleted_at) {
            $response = $hubSpotApi->client->companies()->delete($hubSpotCompany->hub_spot_id);
            $response = $hubSpotApi->cleanResponseData($response->getData());
        } else {
            $data = [];
            $data['console_url'] = Url::to(['/company/view', 'id' => $company->id], 'https');

            // map fields
            $data['name'] = $company->name;
            $data['domain'] = $company->website;
            $data['website'] = $company->website;
            $data['phone'] = $company->phone;
            //$data['fax'] = $company->fax;

            // map sales rep
            if ($company->staffRep) {
                $hubSpotUser = HubSpotUser::findOne(['model_id' => $company->staffRep->id]);
                if ($hubSpotUser) {
                    $data['hubspot_owner_id'] = $hubSpotUser->hub_spot_id;
                }
            }

            // map industry
            $data['industry_internal_use_'] = $company->industry ? $company->industry->name : null;

            // map billing address
            $billingAddress = Address::find()->notDeleted()->andWhere([
                'type' => Address::TYPE_BILLING,
                'model_name' => $company->className(),
                'model_id' => $company->id,
            ])->one();
            if ($billingAddress) {
                $data['address'] = $billingAddress->street;
                //$data['address2'] = $billingAddress->postcode;
                $data['city'] = $billingAddress->city;
                $data['zip'] = $billingAddress->postcode;
                $data['state'] = $billingAddress->state;
                $data['country'] = $billingAddress->country;
            }

            // create/update
            if (!$hubSpotCompany->hub_spot_id) {
                $response = $hubSpotApi->client->companies()->create($hubSpotApi->cleanRequestData($data));
                $response = $hubSpotApi->cleanResponseData($response->getData());
                $hubSpotCompany->hub_spot_id = (string)$response['companyId'];
            } else {
                $response = $hubSpotApi->client->companies()->update($hubSpotCompany->hub_spot_id, $hubSpotApi->cleanRequestData($data));
            }
        }

        // save
        $hubSpotCompany->hub_spot_pushed = time();
        if (!$hubSpotCompany->save()) {
            throw new Exception('cannot save HubSpotCompany [model_id:' . $hubSpotCompany->model_id . '] ' . Helper::getErrorString($hubSpotCompany));
        }

        return $response;
    }

    /**
     * @param $hub_spot_id
     * @param $data
     * @param null $receivedTime
     * @return array|bool
     * @throws Exception
     */
    public static function hubSpotPull($hub_spot_id, $data = null, $receivedTime = null)
    {
        if (YII_ENV != 'prod') return false;

        $hubSpotApi = Yii::$app->hubSpotApi;

        $hubSpotCompany = self::find()->andWhere(['hub_spot_id' => $hub_spot_id])->one();
        if (!$hubSpotCompany) {
            $hubSpotCompany = new HubSpotCompany();
            $hubSpotCompany->hub_spot_id = $hub_spot_id;
        } else {
            if ($receivedTime && $hubSpotCompany->hub_spot_pulled > $receivedTime) {
                return false;
            }
        }

        if ($data === null) {
            $data = $hubSpotApi->cleanResponseData($hubSpotApi->client->companies()->getById($hub_spot_id)->getData());
        }

        // begin transaction
        $transaction = Yii::$app->dbData->beginTransaction();

        // load company
        $company = $hubSpotCompany->model_id ? Company::findOne($hubSpotCompany->model_id) : false;
        if (!$company) {
            $company = new Company();
            $company->loadDefaultValues();
        }
        $updateHubSpot = $company->isNewRecord;

        // map fields
        $company->name = isset($data['properties']['name']) ? $data['properties']['name'] : null;
        $company->phone = isset($data['properties']['phone']) ? $data['properties']['phone'] : null;
        $company->website = !empty($data['properties']['domain']) ? $data['properties']['domain'] : (!empty($data['properties']['website']) ? $data['properties']['website'] : null);
        if (!$company->website) {
            $company->website = 'example.com/' . uniqid();
            $updateHubSpot = true;
        }

        // map sales rep
        $hubSpotUser = !empty($data['properties']['hubspot_owner_id']) ? HubSpotUser::find()->andWhere(['hub_spot_id' => $data['properties']['hubspot_owner_id']])->one() : false;
        if ($hubSpotUser) {
            $company->staff_rep_id = $hubSpotUser->model_id;
        }
        if (!$company->staff_rep_id) {
            $company->staff_rep_id = Job::STAFF_LEAD_DEFAULT;
        }

        // map price structure
        if (!$company->price_structure_id) {
            $company->price_structure_id = PriceStructure::PRICE_STRUCTURE_DEFAULT;
        }

        // map account type
        if (!$company->account_term_id) {
            $company->account_term_id = AccountTerm::ACCOUNT_TERM_DEFAULT;
        }

        // map price structure
        if (!$company->job_type_id) {
            $company->job_type_id = JobType::JOB_TYPE_DEFAULT;
        }

        // map industry
        if (isset($data['properties']['industry_internal_use_'])) {
            $industry = Industry::find()->andWhere(['name' => $data['properties']['industry_internal_use_']])->one();
            if (!$industry) {
                $industry = new Industry();
                $industry->name = $data['properties']['industry_internal_use_'];
                $industry->save(false);
            }
            $company->industry_id = $industry->id;
        }

        // save company
        //$company->hubSpotUpdate = false;
        if (!$company->save()) {
            $transaction->rollBack();
            EmailManager::sendHubSpotCompanyPullError($hub_spot_id, $company, $data);
            throw new Exception('cannot save company [id:' . $company->id . '|hub_spot_id:' . $hub_spot_id . '] ' . Helper::getErrorString($company));
        }

        // save billing address
        $billingAddress = Address::findOne([
            'type' => Address::TYPE_BILLING,
            'model_name' => $company->className(),
            'model_id' => $company->id,
        ]);
        if (!$billingAddress) {
            $billingAddress = new Address();
            $billingAddress->type = Address::TYPE_BILLING;
            $billingAddress->model_name = $company->className();
            $billingAddress->model_id = $company->id;
            $billingAddress->name = $company->name;
        }
        $billingAddress->street = !empty($data['properties']['address']) ? $data['properties']['address'] : 'n/a';
        $billingAddress->postcode = !empty($data['properties']['zip']) ? $data['properties']['zip'] : 'n/a';
        $billingAddress->city = !empty($data['properties']['city']) ? $data['properties']['city'] : 'n/a';
        $billingAddress->state = !empty($data['properties']['state']) ? $data['properties']['state'] : 'n/a';
        $billingAddress->country = !empty($data['properties']['country']) ? $data['properties']['country'] : 'n/a';
        if (!$billingAddress->save(false)) {
            $transaction->rollBack();
            EmailManager::sendHubSpotCompanyAddressPullError($hub_spot_id, $company, $billingAddress, $data);
            throw new Exception('cannot save billingAddress [id:' . $company->id . '|hub_spot_id:' . $hub_spot_id . '] ' . Helper::getErrorString($billingAddress));
        }

        // save HubSpot
        if (!$hubSpotCompany->model_id) {
            $hubSpotCompany->model_id = $company->id;
        }
        $hubSpotCompany->hub_spot_pulled = time();
        if (!$hubSpotCompany->save()) {
            $transaction->rollBack();
            throw new Exception('cannot save HubSpotCompany [model_id:' . $hubSpotCompany->model_id . '] ' . Helper::getErrorString($hubSpotCompany));
        }

        // we did it!
        $transaction->commit();

        // log pull
        Log::log('hubspot company pull', $company);

        // update console_url in hubspot
        if ($updateHubSpot) {
            $hubSpotApi->client->companies()->update($hubSpotCompany->hub_spot_id, $hubSpotApi->cleanRequestData([
                'console_url' => Url::to(['/company/view', 'id' => $company->id], 'https'),
            ]));
        }

        return $data;
    }

}
