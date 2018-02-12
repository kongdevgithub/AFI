<?php

namespace app\models;

use app\components\EmailManager;
use app\components\Helper;
use app\models\query\HubSpotContactQuery;
use SevenShores\Hubspot\Exceptions\BadRequest;
use Yii;
use yii\base\Exception;
use yii\helpers\Url;

/**
 * This is the model class for table "hub_spot".
 */
class HubSpotContact extends HubSpot
{

    /**
     *
     */
    const MODEL_NAME = 'app\models\Contact';

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
     * @return HubSpotContactQuery
     */
    public static function find()
    {
        return new HubSpotContactQuery(get_called_class(), ['model_name' => self::MODEL_NAME]);
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

        $hubSpotContact = self::find()->andWhere(['model_id' => $model_id])->one();
        if (!$hubSpotContact) {
            $hubSpotContact = new HubSpotContact();
            $hubSpotContact->model_id = $model_id;
        }

        $hubSpotApi = Yii::$app->hubSpotApi;
        $contact = Contact::findOne($hubSpotContact->model_id);

        // log push
        Log::log('hubspot contact push', $contact);

        // delete
        if ($contact->deleted_at) {
            $response = $hubSpotApi->client->contacts()->delete($hubSpotContact->hub_spot_id);
            $response = $hubSpotApi->cleanResponseData($response->getData());
        } else {
            $data = [];
            $data['console_url'] = Url::to(['/contact/view', 'id' => $contact->id], 'https');

            // map fields
            $data['firstname'] = $contact->first_name;
            $data['lastname'] = $contact->last_name;
            $data['email'] = $contact->email;
            $data['phone'] = $contact->phone;

            if ($contact->defaultCompany) {
                // map company
                if ($contact->defaultCompany->hubSpotCompany) {
                    $data['associatedcompanyid'] = $contact->defaultCompany->hubSpotCompany->hub_spot_id;
                }

                // map owner
                $hubSpotUser = HubSpotUser::findOne(['hub_spot_id' => $contact->defaultCompany->staff_rep_id]);
                if ($hubSpotUser) {
                    $data['hubspot_owner_id'] = $hubSpotUser->hub_spot_id;
                }
            }

            // create/update
            if (!$hubSpotContact->hub_spot_id) {
                //try {
                $response = $hubSpotApi->client->contacts()->create($hubSpotApi->cleanRequestData($data, 'property'));
                $response = $hubSpotApi->cleanResponseData($response->getData());
                $hubSpotContact->hub_spot_id = (string)$response['vid'];
                //} catch (BadRequest $e) {
                //if ($e->getCode() == 409) {
                //    $response = $hubSpotApi->client->contacts()->getByEmail($contact->email);
                //    $response = $hubSpotApi->cleanResponseData($response->getData());
                //    $hubSpotContact->hub_spot_id = (string)$response['vid'];
                //}
                //if (!$hubSpotContact->hub_spot_id) {
                //    throw $e;
                //}
                //}
            } else {
                $response = $hubSpotApi->client->contacts()->update($hubSpotContact->hub_spot_id, $hubSpotApi->cleanRequestData($data, 'property'));
            }
        }

        // save
        $hubSpotContact->hub_spot_pushed = time();
        if (!$hubSpotContact->save()) {
            throw new Exception('cannot save HubSpotContact [model_id:' . $hubSpotContact->model_id . '] ' . Helper::getErrorString($hubSpotContact));
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

        $hubSpotContact = self::find()->andWhere(['hub_spot_id' => $hub_spot_id])->one();
        if (!$hubSpotContact) {
            $hubSpotContact = new HubSpotContact();
            $hubSpotContact->hub_spot_id = $hub_spot_id;
        } else {
            if ($receivedTime && $hubSpotContact->hub_spot_pulled > $receivedTime) {
                return false;
            }
        }

        if ($data === null) {
            $data = $hubSpotApi->cleanResponseData($hubSpotApi->client->contacts()->getById($hub_spot_id)->getData());
        }
        if (empty($data['properties']['associatedcompanyid'])) {
            return false;
        }

        // begin transaction
        $transaction = Yii::$app->dbData->beginTransaction();

        // load contact
        $contact = $hubSpotContact->model_id ? Contact::findOne($hubSpotContact->model_id) : false;
        if (!$contact) {
            $contact = new Contact();
            $contact->loadDefaultValues();
        }
        $newContact = $contact->isNewRecord;

        // map fields
        $contact->first_name = isset($data['properties']['firstname']) ? $data['properties']['firstname'] : null;
        $contact->last_name = isset($data['properties']['lastname']) ? $data['properties']['lastname'] : null;
        $contact->email = isset($data['properties']['email']) ? $data['properties']['email'] : null;
        $contact->phone = isset($data['properties']['phone']) ? $data['properties']['phone'] : null;

        // validation
        if (!$contact->first_name && $contact->last_name) {
            $contact->first_name = $contact->last_name;
            $contact->last_name = null;
        }

        // map company
        $hubSpotCompany = HubSpotCompany::findOne(['hub_spot_id' => $data['properties']['associatedcompanyid']]);
        if ($hubSpotCompany) {
            $contact->default_company_id = $hubSpotCompany->model_id;
        }

        // save contact
        //$contact->hubSpotUpdate = false;
        if (!$contact->save()) {
            $transaction->rollBack();
            EmailManager::sendHubSpotContactPullError($hub_spot_id, $contact, $data);
            throw new Exception('cannot save Contact [id:' . $contact->id . '|hub_spot_id:' . $hub_spot_id . '] ' . Helper::getErrorString($contact));
        }

        // log pull
        Log::log('hubspot contact pull', $contact);

        // save company default_contact_id
        if ($contact->defaultCompany && !$contact->defaultCompany->default_contact_id) {
            $contact->defaultCompany->default_contact_id = $contact->id;
            $contact->defaultCompany->save(false);
        }

        // save HubSpotContact
        if (!$hubSpotContact->model_id) {
            $hubSpotContact->model_id = $contact->id;
        }
        $hubSpotContact->hub_spot_pulled = time();
        if (!$hubSpotContact->save()) {
            $transaction->rollBack();
            throw new Exception('cannot save HubSpotContact [model_id:' . $hubSpotContact->model_id . '] ' . Helper::getErrorString($hubSpotContact));
        }

        // we did it!
        $transaction->commit();

        // log pull
        Log::log('hubspot pull', $contact);

        // update console_url in hubspot
        if ($newContact) {
            $hubSpotApi->client->contacts()->update($hubSpotContact->hub_spot_id, $hubSpotApi->cleanRequestData([
                'console_url' => Url::to(['/contact/view', 'id' => $contact->id], 'https'),
            ], 'property'));
        }

        return $data;
    }

}
