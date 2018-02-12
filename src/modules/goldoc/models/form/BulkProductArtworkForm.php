<?php

namespace app\modules\goldoc\models\form;

use app\components\Helper;
use app\models\Attachment;
use app\modules\goldoc\models\Product;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Class BulkProductArtworkForm
 * @package app\models\form
 *
 * @property Attachment $artwork
 */
class BulkProductArtworkForm extends Model
{

    /**
     * @var
     */
    public $ids;

    /**
     * @var Attachment
     */
    private $_artwork;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['ids', 'Artwork'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function setAttributes($values, $safeOnly = true)
    {
        unset($values['ru']);
        unset($values['_csrf']);
        if (isset($values['BulkProductArtworkForm'])) {
            foreach ($values['BulkProductArtworkForm'] as $k => $v) {
                $values[$k] = $v;
            }
            unset($values['BulkProductArtworkForm']);
        }
        parent::setAttributes($values, $safeOnly);
    }

    /**
     *
     */
    public function afterValidate()
    {
        $error = false;
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    /**
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->dbData->beginTransaction();

        /** @var bool|Attachment $artwork */
        $artwork = false;

        foreach ($this->ids as $id) {
            $product = Product::findOne($id);
            if ($product->artwork) {
                $product->artwork->delete();
            }

            if (!$artwork) {
                $artwork = new Attachment();
                $artwork->model_name = $product->className() . '-Artwork';
                $artwork->model_id = $product->id;
                $artwork->upload = UploadedFile::getInstance($this->artwork, 'upload');

                if ($artwork->upload) {
                    if (!$artwork->upload('artwork-' . uniqid() . '-' . $artwork->upload->name)) {
                        $this->addError('ids', Yii::t('goldoc', 'Cannot upload Artwork for Product :product_id', [
                            'product_id' => $product->id,
                        ]));
                        $transaction->rollBack();
                        return false;
                    }
                    if (!$artwork->save()) {
                        $this->addError('ids', Yii::t('goldoc', 'Cannot save Artwork for Product :product_id: :errors', [
                            'product_id' => $product->id,
                            'errors' => Helper::getErrorString($artwork),
                        ]));
                        $transaction->rollBack();
                        return false;
                    }
                    $product->clearCache();
                }
            } else {
                $artwork->copy([
                    'Attachment' => [
                        'model_name' => $product->className() . '-Artwork',
                        'model_id' => $product->id,
                    ],
                ]);
            }

        }

        $transaction->commit();
        return true;
    }

    /**
     * @return Attachment
     */
    public function getArtwork()
    {
        if (!$this->_artwork) {
            $this->_artwork = new Attachment();
            $this->_artwork->loadDefaultValues();
        }
        return $this->_artwork;
    }

    /**
     * @param Attachment|array $artwork
     */
    public function setArtwork($artwork)
    {
        if ($artwork instanceof Product) {
            $this->_artwork = $artwork;
        } else if (is_array($artwork)) {
            $this->artwork->setAttributes($artwork);
        }
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        $products = [];
        foreach ($this->ids as $id) {
            $product = Product::findOne($id);
            if ($product) {
                $products[] = $product;
            }
        }
        return $products;
    }

}