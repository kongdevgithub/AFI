<?php

namespace app\components\behaviors;

use app\components\Helper;
use app\components\ReturnUrl;
use kartik\select2\Select2Asset;
use kartik\select2\ThemeKrajeeAsset;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\helpers\WorkflowHelper;
use Yii;
use yii\bootstrap\Button;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * Class WorkflowBehavior
 * @package app\components\behaviors
 *
 * @property ActiveRecord|WorkflowBehavior $owner
 * @property string $defaultWorkflowId
 */
class WorkflowBehavior extends SimpleWorkflowBehavior
{

    /**
     * @var bool
     */
    public $propagateErrorsToModel = true;

    /**
     * Returns the items and options for a dropDownList
     * All status options are in the list, but invalid transitions are disabled
     *
     * Example:
     * $statusDropDownData = $model->getStatusDropDownData();
     * echo $form->field($model, 'status')
     *     ->dropDownList($statusDropdown['items'], ['options' => $statusDropdown['options']]);
     *
     * @param bool $beforeEvents
     * @return array
     */
    public function getStatusDropDownData($beforeEvents = true)
    {
        $transitions = array_keys(WorkflowHelper::getNextStatusListData($this->owner, false, $beforeEvents));
        /** @var Status[] $items */
        $items = $this->owner->getWorkflowSource()->getAllStatuses($this->owner->getWorkflow()->getId());
        $options = [];
        foreach ($items as $status) {
            $metadata = $status->getMetadata();
            if (!empty($metadata['color'])) {
                $options[$status->getId()]['data-color'] = $metadata['color'];
            }
            if (!empty($metadata['icon'])) {
                $options[$status->getId()]['data-icon'] = $metadata['icon'];
            }
            if ($status->getId() != $this->owner->getWorkflowStatus()->getId() && !in_array($status->getId(), $transitions)) {
                $options[$status->getId()]['disabled'] = true;
            }
        }
        return [
            'items' => ArrayHelper::map($items, 'id', 'label'),
            'options' => $options,
        ];
    }

    /**
     * Returns a HTML label representing the status
     *
     * @param array $options
     * @return string
     */
    public function getStatusButton($options = [])
    {
        static $registered;
        if (!$registered) {
            $registered = true;
            Select2Asset::register(Yii::$app->view);
            ThemeKrajeeAsset::register(Yii::$app->view);
        }
        $workflowStatus = $this->owner->getWorkflowStatus();
        $metadata = $workflowStatus->getMetadata();
        $background = !empty($metadata['background']) ? 'background: ' . $metadata['background'] . ';' : '';
        $color = !empty($metadata['color']) ? 'color: ' . $metadata['color'] . ';' : '';
        $icon = !empty($metadata['icon']) ? '<span class="' . $metadata['icon'] . '"></span>' : $workflowStatus->getLabel();

        $quantity = 0;
        if (isset($options['quantity'])) {
            $quantity = $options['quantity'];
        } else if ($this->owner->hasAttribute('quantity')) {
            $quantity = $this->owner->getAttribute('quantity');
        }
        if ($quantity) {
            $icon .= '<span class="label label-primary">' . $quantity . '</span>';
        }

        if (!empty($options['title'])) {
            $title = $options['title'];
        } else {
            $workflow = explode('/', $workflowStatus->getId())[0];
            $title = Inflector::camel2words($workflow) . ' ' . $workflowStatus->getLabel();
            if ($quantity) {
                $title .= ' (' . $quantity . ')';
            }
        }

        $modelName = substr(substr($this->owner->className(), strrpos($this->owner->className(), '\\') + 1), 0);
        $controllerName = Inflector::camel2id($modelName);
        $url = [
            $controllerName . '/status',
            'id' => $this->owner->primaryKey,
            'ru' => Yii::$app->request->isAjax && ReturnUrl::getRequestToken() ? ReturnUrl::getRequestToken() : ReturnUrl::getToken(),
            //$modelName => [$this->statusAttribute => $this->owner->getNextStatus()],
        ];
        $link = Html::a($icon, $url, [
            'class' => 'modal-remote btn btn-default btn-sm btn-status',
            'style' => $background . $color,
            'title' => $title,
            'data-toggle' => 'tooltip',
        ]);
        return $link;
    }

    /**
     * Returns the status string of the next valid status from the list of transitions
     *
     * @return string
     */
    public function getNextStatus()
    {
        $currentStatus = $this->owner->getAttribute($this->statusAttribute);
        $statusList = $this->owner->getWorkflowSource()->getAllStatuses($this->owner->getWorkflow()->getId());
        $transitions = array_keys(WorkflowHelper::getNextStatusListData($this->owner, false, true));
        $started = false;
        foreach ($statusList as $status) {
            $status_id = $status->getId();
            if ($status_id == $currentStatus) {
                $started = true;
            }
            if ($started) {
                if (in_array($status_id, $transitions) && $this->owner->isValidNextStatus($status_id)) {
                    return $status_id;
                }
            }
        }
        return $currentStatus;
    }

    /**
     * Checks if a given status is a valid transition from the current status
     *
     * @param string $status_id
     * @return bool
     */
    public function isValidNextStatus($status_id)
    {
        $eventSequence = $this->owner->getEventSequence($status_id);
        foreach ($eventSequence['before'] as $event) {
            if ($this->owner->hasEventHandlers($event->name)) {
                $this->owner->trigger($event->name, $event);
                if ($event->isValid === false) {
                    return false;
                }
            }
        }
        return true;
    }

}