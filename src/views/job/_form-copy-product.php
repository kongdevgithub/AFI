<?php

use app\components\quotes\jobs\BaseJobQuote;
use app\models\AccountTerm;
use app\models\Company;
use app\models\Job;
use app\models\JobType;
use app\models\PriceStructure;
use app\models\Rollout;
use app\models\Contact;
use app\models\User;
use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use dosamigos\tinymce\TinyMce;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap\Collapse;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var app\models\form\JobForm $jobForm
 * @var app\models\Product $product
 * @var app\models\Job $product
 * @var ActiveForm $form
 */

echo $form->field($jobForm, "productsMeta[$product->id][copy_notes]")->checkbox(['label' => Yii::t('app', 'Copy Product Notes')]);
echo $form->field($jobForm, "productsMeta[$product->id][copy_attachments]")->checkbox(['label' => Yii::t('app', 'Copy Product Attachments')]);
