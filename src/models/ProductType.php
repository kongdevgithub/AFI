<?php

namespace app\models;

use bedezign\yii2\audit\AuditTrailBehavior;
use cornernote\cachebehavior\CacheBehavior;
use cornernote\linkbehavior\LinkBehavior;
use cornernote\softdelete\SoftDeleteBehavior;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * This is the model class for table "product_type".
 *
 * @property Attachment[] $attachments
 * @property Note[] $notes
 *
 * @mixin LinkBehavior
 * @mixin CacheBehavior
 */
class ProductType extends base\ProductType
{

    /**
     *
     */
    const PRODUCT_TYPE_SPARE_PART = 122;

    /**
     *
     */
    const PRODUCT_TYPE_GC2018 = 142;
    /**
     *
     */
    const PRODUCT_TYPE_OCTAWALL_40MM = 138;

    /**
     * @var
     */
    private $_breadcrumbHtml;

    /**
     * @var
     */
    private $_breadcrumbString;


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = LinkBehavior::className();
        $behaviors[] = AuditTrailBehavior::className();
        $behaviors[] = [
            'class' => CacheBehavior::className(),
            'backupCache' => 'cacheFile',
            //'cacheRelations' => ['product'],
        ];
        $behaviors[] = TimestampBehavior::className();
        $behaviors[] = SoftDeleteBehavior::className();
        return $behaviors;
    }

    /**
     * @var UploadedFile
     */
    public $imageFile;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => ['id', 'parent_id', 'name', 'image', 'quote_class', 'sort_order', 'deleted_at', 'complexity', 'config', 'created_at', 'updated_at'],
            'create' => ['id', 'parent_id', 'name', 'image', 'quote_class', 'sort_order', 'deleted_at', 'complexity', 'config', 'created_at', 'updated_at'],
            'update' => ['id', 'parent_id', 'name', 'image', 'quote_class', 'sort_order', 'deleted_at', 'complexity', 'config', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductType::className(), 'targetAttribute' => ['parent_id' => 'id']],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, gif'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $this->imageFile = UploadedFile::getInstance($this, 'imageFile');
        return parent::load($data, $formName);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!$this->isNewRecord && $this->imageFile) {
            $this->image = $this->id . '.' . $this->imageFile->extension;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->imageFile) {
            Yii::$app->s3->upload('product-type/' . $this->id . '.' . $this->imageFile->extension, $this->imageFile->tempName);
            if (!$this->image) {
                $this->image = $this->id . '.' . $this->imageFile->extension;
                $this->imageFile = null;
                $this->save(false);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return string
     */
    public function getImageSrc()
    {
        return $this->image ? Yii::$app->params['s3BucketUrl'] . '/product-type/' . $this->image : '';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $attributeLabels = parent::attributeLabels();
        $attributeLabels['parent_id'] = Yii::t('app', 'Parent');
        return $attributeLabels;
    }

    /**
     * @param ProductType[] $productTypes
     * @return ProductType[]
     */
    public function getBreadcrumb($productTypes = [])
    {
        if ($this->parent && $this->parent_id != $this->id && !in_array($this->parent_id, array_keys($productTypes))) {
            $productTypes = $this->parent->getBreadcrumb($productTypes);
        }
        $productTypes[$this->id] = $this;
        return $productTypes;
    }

    /**
     * @param $parent_id
     * @return bool
     */
    public function hasParent($parent_id)
    {
        foreach ($this->getBreadcrumb() as $breadcrumb) {
            if ($breadcrumb->id == $parent_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getBreadcrumbHtml($delimiter = ' &raquo; ')
    {
        if (!$this->_breadcrumbHtml) {
            $this->_breadcrumbHtml = [];
            $breadcrumb = $this->getCache('getBreadcrumbHtml');
            if (!$breadcrumb) {
                $breadcrumb = [];
                foreach ($this->getBreadcrumb() as $_breadcrumb) {
                    $breadcrumb[] = Html::a($_breadcrumb->name, ['/product-type/view', 'id' => $_breadcrumb->id]);
                }
            }
            $this->setCache('getBreadcrumbHtml', $breadcrumb);
            $this->_breadcrumbHtml = $breadcrumb;
        }
        return implode($delimiter, $this->_breadcrumbHtml);
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getBreadcrumbString($delimiter = ' &raquo; ')
    {
        if (!$this->_breadcrumbString) {
            $this->_breadcrumbString = [];
            $breadcrumb = $this->getCache('_breadcrumbString');
            if (!$breadcrumb) {
                $breadcrumb = [];
                foreach ($this->getBreadcrumb() as $_breadcrumb) {
                    $breadcrumb[] = $_breadcrumb->name;
                }
            }
            $this->setCache('_breadcrumbString', $breadcrumb);
            $this->_breadcrumbString = $breadcrumb;
        }
        return implode($delimiter, $this->_breadcrumbString);
    }

    /**
     * @param int $parent_id
     * @return array
     */
    public static function getDropdownOpts($parent_id = null)
    {
        $productTypes = [];
        $_productTypes = ProductType::find()
            ->notDeleted()
            ->andWhere(['parent_id' => $parent_id])
            ->orderBy('name')
            ->all();
        foreach ($_productTypes as $productType) {
            $productTypes[] = $productType;
            $productTypes = $productType->getDropdownOptsChildren($productTypes, $productType->name . ' > ');
        }
        return ArrayHelper::map($productTypes, 'id', 'name');
    }

    /**
     * @param $productTypes
     * @param string $prefix
     * @return array
     */
    public function getDropdownOptsChildren($productTypes, $prefix = '')
    {
        foreach ($this->productTypes as $productType) {
            $productType->name = $prefix . $productType->name;
            $productTypes[] = $productType;
            $productTypes = $productType->getDropdownOptsChildren($productTypes, $productType->name . ' > ');
        }
        return $productTypes;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->productTypes as $productType) {
            $productType->delete();
        }
        foreach ($this->productTypeToOptions as $productTypeToOption) {
            $productTypeToOption->delete();
        }
        foreach ($this->productTypeToItemTypes as $productTypeToItemType) {
            $productTypeToItemType->delete();
        }
        return parent::beforeDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypes()
    {
        return $this->hasMany(ProductType::className(), ['parent_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToItemTypes()
    {
        return $this->hasMany(ProductTypeToItemType::className(), ['product_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToComponents()
    {
        return $this->hasMany(ProductTypeToComponent::className(), ['product_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductTypeToOptions()
    {
        return $this->hasMany(ProductTypeToOption::className(), ['product_type_id' => 'id'])
            ->andWhere('deleted_at IS NULL')
            ->orderBy(['product_type_to_item_type_id' => SORT_ASC, 'sort_order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        $relation = Attachment::find()
            ->andWhere('attachment.deleted_at IS NULL')
            ->orOnCondition([
                'attachment.model_id' => $this->id,
                'attachment.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        $relation = Note::find()
            ->andWhere('note.deleted_at IS NULL')
            ->orOnCondition([
                'note.model_id' => $this->id,
                'note.model_name' => $this->className(),
            ])->orderBy(['sort_order' => SORT_ASC]);
        $relation->multiple = true;
        $relation->link = ['model_id' => 'id'];
        return $relation;
    }

    /**
     * @return array
     */
    public static function complexityOpts()
    {
        return [
            '0' => Yii::t('app', 'Simplified'),
            '1' => Yii::t('app', 'Modified'),
            '2' => Yii::t('app', 'Bespoke'),
        ];
    }

    /**
     * @param string $type - read or
     * @return bool
     */
    public function checkAccess($type = null)
    {
        $permissionName = '_product-type_' . $this->id;
        if ($type && Yii::$app->user->can($permissionName . '_' . $type)) {
            return true;
        }
        if (Yii::$app->user->can($permissionName)) {
            return true;
        }
        return Yii::$app->user->can('_product-type');
    }

    /**
     * @return array|null
     */
    public function getConfigDecoded()
    {
        return Json::decode($this->config);
    }

    /**
     * @param array|null $config
     */
    public function setConfigDecoded($config)
    {
        $this->config = Json::encode($config, JSON_PRETTY_PRINT);
    }

}
