<?php

namespace app\commands;

use app\components\CommandStats;
use app\components\Csv;
use app\models\Note;
use app\models\Profile;
use app\models\User;
use app\modules\goldoc\models\Colour;
use app\modules\goldoc\models\Design;
use app\modules\goldoc\models\Item;
use app\modules\goldoc\models\Product;
use app\modules\goldoc\models\Sponsor;
use app\modules\goldoc\models\Substrate;
use app\modules\goldoc\models\Supplier;
use app\modules\goldoc\models\Venue;
use Yii;
use yii\console\Controller;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class GoldocImportController
 * @package app\commands
 */
class GoldocImportController extends Controller
{

    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout("Deleting Products\n");
        $products = Product::find()
            ->notDeleted()
            ->all();
        $count = count($products);
        foreach ($products as $k => $product) {
            $this->stdout(CommandStats::stats($k + 1, $count) . "\n");
            $product->delete();
        }

        $this->stdout("Importing Products\n");

        //$transaction = Yii::$app->dbGoldoc->beginTransaction();

        $data = Csv::csvToArray(Yii::getAlias('@data/goldoc/GC2018_IMS FINAL_active_3Nov17.csv'));
        $count = count($data);
        foreach ($data as $k => $row) {

            /**
             * Venue
             * Mgr
             * LOC
             * KOP Description
             * W
             * H
             * Product Description
             * Qty
             * Loc Notes
             * Install
             * ITEM CODE
             * COLOUR
             * DESIGN
             * SUBSTRATE
             * SIZE
             */
            $this->stdout(CommandStats::stats($k + 1, $count) . "\n");

            $product = new Product();
            $product->venue_id = $this->getModelId($row['Venue'], Venue::className());
            $product->loc = $row['LOC'];
            $product->goldoc_manager_id = $this->getManagerId($row['Mgr'], 'goldoc-goldoc');
            //$product->active_manager_id = $this->getManagerId('-void-', 'goldoc-active');
            //$product->sport_id = $this->getModelId($row['Sport'], Sport::className());
            $product->width = $row['W'] ? (int)$row['W'] : null;
            $product->height = $row['H'] ? (int)$row['H'] : null;
            $product->depth = $row['D'] ? (int)$row['D'] : null;
            $product->quantity = $row['Qty'];
            //$product->details = $row['Product Finishing/Construction'];
            $product->item_id = $this->getModelId($row['ITEM CODE'], Item::className());
            $product->colour_id = $this->getModelId($row['COLOUR'], Colour::className());
            $product->design_id = $this->getModelId($row['DESIGN'], Design::className());
            $product->substrate_id = $this->getModelId($row['SUBSTRATE'], Substrate::className());
            if (!$product->save(false)) {
                print_r($product->errors);
            }

            if (!empty(trim($row['Comments']))) {
                $note = new Note();
                $note->model_name = $product->className() . '-Goldoc';
                $note->model_id = $product->id;
                $note->important = 0;
                $note->title = 'Comments';
                $note->body = trim($row['Comments']);
                if (!$note->save()) {
                    print_r($note->errors);
                }
            }

            if (!empty(trim($row['Product Description']))) {
                $note = new Note();
                $note->model_name = $product->className() . '-Production';
                $note->model_id = $product->id;
                $note->important = 0;
                $note->title = 'Product Description';
                $note->body = trim($row['Product Description']);
                if (!$note->save()) {
                    print_r($note->errors);
                }
            }
            if (!empty(trim($row['Loc Notes']))) {
                $note = new Note();
                $note->model_name = $product->className() . '-Installation';
                $note->model_id = $product->id;
                $note->important = 0;
                $note->title = 'LOC Notes';
                $note->body = trim($row['Loc Notes']);
                if (!$note->save()) {
                    print_r($note->errors);
                }
            }
            if (!empty(trim($row['Install']))) {
                $note = new Note();
                $note->model_name = $product->className() . '-Installation';
                $note->model_id = $product->id;
                $note->important = 0;
                $note->title = 'Install';
                $note->body = trim($row['Install']);
                if (!$note->save()) {
                    print_r($note->errors);
                }
            }
        }

        //$transaction->commit();

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * @param $string
     * @param $class
     * @return int
     */
    private function getModelId($string, $class)
    {
        $data = explode(' - ', trim($string));
        if (!$data[0]) {
            return null;
        }
        $code = $data[0];
        /** @var ActiveRecord|Item|Colour|Design|Substrate|Supplier|Sponsor $model */
        /** @var ActiveRecord $class */
        $model = $class::findOne(['code' => $code]);
        if (!$model) {
            $model = new $class;
            $model->code = $code;
            $model->name = isset($data[1]) ? $data[1] : '-unknown-';
            $model->save(false);
        }
        return $model ? $model->id : null;
    }

    /**
     * @param $string
     * @return int
     */
    private function getManagerId($string, $role)
    {
        $profile = Profile::findOne(['name' => trim($string)]);
        if ($profile) {
            $user = $profile->user;
        } else {
            $user = User::find()->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole($role)])->orderBy(new Expression('RAND()'))->one();
        }
        if (!$user) {
            $user = User::find()->andWhere(['id' => Yii::$app->authManager->getUserIdsByRole('goldoc')])->orderBy(new Expression('RAND()'))->one();
        }
        return $user ? $user->id : null;
    }

}