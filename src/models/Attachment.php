<?php

namespace app\models;

use app\components\Helper;
use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Imagick;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * This is the model class for table "attachment".
 */
class Attachment extends base\Attachment
{

    /**
     * @var UploadedFile
     */
    public $upload;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['upload'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, gif, pdf, zip'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (Yii::$app instanceof \yii\web\Application) {
            $this->updated_by = Yii::$app->user->id;
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * @param $filename
     * @return bool
     */
    public function upload($filename = null)
    {
        $s3 = Yii::$app->s3;
        if (!$this->upload) {
            $this->upload = UploadedFile::getInstance($this, 'upload');
        }
        if (!$this->validate()) {
            return false;
        }
        $filename = $filename ?: $this->upload->name;
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $this->filename = Inflector::slug(basename(str_replace('_', '-', $filename), '.' . $ext), '', false);
        $this->extension = $ext;
        $this->filesize = $this->upload->size;
        $this->filetype = $this->upload->type;
        $filePath = $this->getFileSrc();
        $i = 0;
        while ($s3->exist($filePath)) {
            $i++;
            $this->filename .= '-' . $i;
            $filePath = $this->getFileSrc();
        }
        $localImage = Yii::$app->runtimePath . '/' . $this->getFileSrc();
        FileHelper::createDirectory(dirname($localImage));
        $this->upload->saveAs($localImage);
        if (!$s3->upload($filePath, $localImage)) {
            return false;
        }
        $this->thumb();
        return true;
    }

    /**
     * @param $size
     * @return bool
     */
    public function thumb($size = null)
    {
        if (!$size) {
            $this->thumb('100x100');
            $this->thumb('300x300');
            $this->thumb('800x800');
            return true;
        }
        $s3 = Yii::$app->s3;
        $image = $this->getFileSrc();
        $localImage = $this->getLocalFile();

        list($width, $height) = explode('x', $size);
        $thumb = $image . '.thumb.' . $width . 'x' . $height . '.jpg';
        $localThumb = Yii::$app->runtimePath . '/' . $thumb;
        FileHelper::createDirectory(dirname($localThumb));

        $im = new Imagick();
        if (!file_exists($localImage)){
            throw new \Exception('attachment local image does not exist ' . $localImage);
        }
        $localImage = realpath($localImage);
        $im->readImage($localImage);

        $_width = $im->getImageWidth();
        if ($_width < $width)
            $width = $_width;
        $_height = $im->getImageHeight();
        if ($_height < $height)
            $height = $_height;

        $im->thumbnailImage((int)$width, (int)$height, true, false);
        if (!$im->writeImages($localThumb, false)) {
            return false;
        }
        if (!file_exists($localThumb)) {
            $localThumb = Yii::$app->runtimePath . '/' . $image . '.thumb.' . $width . 'x' . $height . '-0.jpg';
            if (!file_exists($localThumb)) {
                return false;
            }
        }

        // double check the size (seems only the last page of the PDF becomes the right size)
        $im = new Imagick();
        $im->readImage($localThumb);
        if ($im->getImageWidth() != $width && $im->getImageHeight() != $height) {
            $im->thumbnailImage((int)$width, (int)$height, true, false);
            if (!$im->writeImages($localThumb, false)) {
                return false;
            }
        }

        $s3->upload($thumb, $localThumb);
        return true;
    }

    /**
     * @return bool|string
     */
    public function getLocalFile()
    {
        $fileSrc = $this->getFileSrc();
        $localFile = Yii::$app->runtimePath . '/' . $fileSrc;
        if (!file_exists($localFile)) {
            FileHelper::createDirectory(dirname($localFile));
            if (!Yii::$app->s3->get($fileSrc, $localFile)) {
                return false;
            }
        }
        return $localFile;
    }

    /**
     * @param array $attributes
     * @return Attachment|bool
     * @throws Exception
     */
    public function copy($attributes = [])
    {
        $s3 = Yii::$app->s3;
        $attachment = new Attachment();
        $attachment->loadDefaultValues();
        $attachment->attributes = $this->attributes;
        $attachment->id = null;
        $attachment->model_name = $attributes['Attachment']['model_name'];
        $attachment->model_id = $attributes['Attachment']['model_id'];
        $allowedAttributes = [
        ];
        if (!empty($attributes['Attachment'])) {
            foreach ($allowedAttributes as $attribute) {
                if (array_key_exists($attribute, $attributes['Attachment'])) {
                    $attachment->$attribute = $attributes['Attachment'][$attribute];
                }
            }
        }
        if (!$attachment->save()) {
            throw new Exception('cannot copy Attachment-' . $this->id . 'F: ' . Helper::getErrorString($attachment));
        }
        $oldPath = $this->getFileSrc();
        $newPath = $attachment->getFileSrc();

        $localFile = Yii::$app->runtimePath . '/' . $newPath;
        FileHelper::createDirectory(dirname($localFile));
        if (!$s3->get($oldPath, $localFile)) {
            throw new Exception('cannot get Attachment from S3');
        }
        if (!$s3->upload($newPath, $localFile)) {
            throw new Exception('cannot upload Attachment to S3');
        }
        $attachment->thumb();

        return $attachment;
    }

    /**
     * @param null $size
     * @return string
     */
    public function getFileUrl($size = null)
    {
        return Yii::$app->params['s3BucketUrl'] . '/' . $this->getFileSrc($size);
    }

    /**
     * @param null $size
     * @return string
     */
    public function getFileSrc($size = null)
    {
        $sizeString = $size ? '.thumb.' . $size . '.jpg' : '';
        $modelName = substr($this->model_name, strrpos($this->model_name, '\\') + 1);
        return implode('/', [
            'attachment',
            $modelName,
            $this->model_id,
            md5(implode(',', [$this->model_id, $this->filesize, '5IPt6Pm7I5n81lw'])), // secret hash
            $this->filename . '.' . $this->extension . $sizeString,
        ]);
    }

    /**
     * @param null $size
     * @return string
     */
    public function getFilePath($size = null)
    {
        $sizeString = $size ? '.thumb.' . $size . '.jpg' : '';
        $fileSrc = 'attachment/' . str_replace('\\', '-', $this->model_name) . '/' . $this->model_id . '/' . $this->filename . '.' . $this->extension . $sizeString;
        return Yii::getAlias('@webroot') . '/uploads/' . $fileSrc;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Helper::clearRelatedCache($this);
        parent::afterSave($insert, $changedAttributes);
    }
}
