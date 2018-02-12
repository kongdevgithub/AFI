<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Company;
use app\models\Contact;
use app\models\form\SupportForm;
use app\models\Item;
use app\models\Job;
use app\models\Package;
use app\models\Pickup;
use app\models\Product;
use app\models\Unit;
use app\traits\AccessBehaviorTrait;
use app\components\ReturnUrl;
use app\traits\TwoFactorTrait;
use Yii;
use yii\caching\Cache;
use yii\db\ActiveRecord;

/**
 * Site controller.
 */
class SiteController extends Controller
{
    use AccessBehaviorTrait;

    //use TwoFactorTrait; // disabled for goldoc/client redirects

    /**
     * Renders the start page.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->can('staff')) {
            $roles = Yii::$app->user->identity->getRoles();
            if (in_array('client', $roles)) {
                return $this->redirect(['client/default/index']);
            }
            if (in_array('goldoc', $roles) || in_array('goldoc-active', $roles) || in_array('goldoc-goldoc', $roles) || in_array('goldoc-goldoc-manager', $roles) || in_array('goldoc-active-manager', $roles)) {
                return $this->redirect(['goldoc/default/index']);
            }
        }
        return $this->render('index');
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSupport()
    {
        $model = new SupportForm();
        if ($model->load(Yii::$app->request->post()) && $model->sendSupportEmail()) {
            Yii::$app->session->setFlash('contactFormSubmitted');
            return $this->refresh();
        }
        return $this->render('support', [
            'model' => $model,
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionStaff()
    {
        return $this->render('staff');
    }

    /**
     * @param null $keywords
     * @return string
     */
    public function actionSearch($keywords = null)
    {
        $modelMap = [
            'quote' => [
                'className' => Job::className(),
                'route' => '//job/quote',
            ],
            'job' => [
                'className' => Job::className(),
                'route' => '//job/production',
            ],
            'despatch' => [
                'className' => Job::className(),
                'route' => '//job/despatch',
            ],
            'finance' => [
                'className' => Job::className(),
                'route' => '//job/finance',
            ],
            'product' => [
                'className' => Product::className(),
                'route' => '//product/view',
            ],
            'item' => [
                'className' => Item::className(),
                'route' => '//item/view',
            ],
            'unit' => [
                'className' => Unit::className(),
                'route' => '//unit/view',
            ],
            'package' => [
                'className' => Package::className(),
                'route' => '//package/view',
            ],
            'pickup' => [
                'className' => Pickup::className(),
                'route' => '//pickup/view',
            ],
            'company' => [
                'className' => Company::className(),
                'route' => '//company/view',
            ],
            'contact' => [
                'className' => Contact::className(),
                'route' => '//contact/view',
            ],
        ];
        $understood = false;
        if (is_numeric($keywords)) {
            $understood = true;
            /** @var Job $className */
            $model = Job::findOne($keywords);
            if ($model) {
                $this->redirect(['//job/view', 'id' => $model->primaryKey]);
            }
        }
        if (strpos($keywords, 'v')) {
            list($id, $version) = explode('v', $keywords);
            if (is_numeric($id) && is_numeric($version)) {
                $understood = true;
                /** @var Job $className */
                $model = Job::findOne($id);
                if ($model) {
                    $versions = $model->getForkTopParent()->getForkVersionIds();
                    $job_id = isset($versions[$version - 2]) ? $versions[$version - 2] : $model->id;
                    $this->redirect(['//job/view', 'id' => $job_id]);
                }
            }
        }
        if (strpos($keywords, '-')) {
            $input = explode('-', strtolower($keywords));
            if (count($input) == 2 && is_numeric($input[1]) && isset($modelMap[$input[0]])) {
                $understood = true;
                $info = $modelMap[$input[0]];
                /** @var ActiveRecord $className */
                $className = $info['className'];
                $model = $className::findOne($input[1]);
                if ($model) {
                    $this->redirect([$info['route'], 'id' => $model->primaryKey]);
                }
            }
        }
        return $this->render('search', [
            'keywords' => $keywords,
            'modelMap' => $modelMap,
            'understood' => $understood,
        ]);
    }

    /**
     * Clears server cache
     * @return string
     */
    public function actionClearCache()
    {
        $redirect = ReturnUrl::getUrl(['/site/index']);
        foreach (Yii::$app->getComponents() as $name => $component) {
            if ($component instanceof Cache) {
                Yii::$app->get($name)->flush();
            } elseif (is_array($component) && isset($component['class']) && is_subclass_of($component['class'], Cache::className())) {
                Yii::$app->get($name)->flush();
            } elseif (is_string($component) && is_subclass_of($component, Cache::className())) {
                Yii::$app->get($name)->flush();
            }
        }
        Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Server cache has been cleared.'));
        return $this->redirect($redirect);
    }

}
