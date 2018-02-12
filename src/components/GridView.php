<?php

namespace app\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @property int $defaultPageSize
 */
class GridView extends \kartik\grid\GridView
{

    /**
     * @var array
     */
    public $panelDefault = [
        'footer' => false,
        'before' => '{pager}{gridActions}{multiActions}',
        'after' => '{pager}{gridActions}{multiActions}',
        'type' => GridView::TYPE_DEFAULT,
    ];

    /**
     * @var array pageSizeLimit for the pagination service
     */
    public $pageSizeLimit = [1, 100000];

    /**
     * @var int defaultPageSize for the pagination service and the Pagesize widget
     */
    private $_defaultPageSize;

    /**
     * @var array
     */
    public $gridActions = [];

    /**
     * @var array
     */
    public $multiActions = [];

    /**
     * @var bool
     */
    public $toolbar = false;

    /**
     * @inheritdoc
     */
    public $panelBeforeTemplate = <<< HTML
    {before}
    <div class="clearfix"></div>
HTML;

    /**
     * @inheritdoc
     */
    public $panelAfterTemplate = <<< HTML
    {after}
    <div class="clearfix"></div>
HTML;

    /**
     * @var string the template for rendering the panel heading. The following special tokens are
     * recognized and will be replaced:
     * - `{heading}`: _string_, which will render the panel heading content.
     * - `{summary}`: _string_, which will render the grid results summary.
     * - `{items}`: _string_, which will render the grid items.
     * - `{pager}`: _string_, which will render the grid pagination links.
     * - `{sort}`: _string_, which will render the grid sort links.
     * - `{toolbar}`: _string_, which will render the [[toolbar]] property passed
     * - `{export}`: _string_, which will render the [[export]] menu button content.
     */
    public $panelHeadingTemplate = <<< HTML
    <div class="pull-right">
        {summary} {pageSize}
    </div>
    <h3 class="panel-title">
        {heading}
    </h3>
    <div class="clearfix"></div>
HTML;

    /**
     * @inheritdoc
     */
    public $striped = false;

    /**
     * @inheritdoc
     */
    public $condensed = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // setup per-page size
        $this->dataProvider->getPagination()->pageSizeLimit = $this->pageSizeLimit;
        $this->dataProvider->getPagination()->defaultPageSize = $this->getDefaultPageSize();

        // setup panel defaults
        $this->panel = ArrayHelper::merge($this->panelDefault, $this->panel);
    }

    /**
     * @inheritdoc
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{pageSize}':
                return $this->renderPageSize();
            case '{gridActions}':
                return $this->renderGridActions();
            case '{multiActions}':
                return $this->renderMultiActions();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->filterSelector = 'select[name="' . $this->id . '-per-page"]';
        parent::run();
    }

    /**
     * Renders the PageSize widget.
     * @return string the rendering result
     */
    public function renderPageSize()
    {
        if ($this->dataProvider->getCount() <= 0) {
            return '';
        }
        return Html::tag('div', PageSize::widget([
            'defaultPageSize' => $this->dataProvider->getPagination()->defaultPageSize,
            'pageSizeParam' => $this->id . '-per-page',
        ]), ['class' => 'page-size']);
    }

    /**
     * Renders the grid actions.
     * @return string the rendering result
     */
    public function renderGridActions()
    {
        if (!$this->gridActions) {
            return '';
        }
        return Html::ul($this->gridActions, ['encode' => false, 'class' => 'grid-actions']);
    }

    /**
     * Renders the multi actions.
     * @return string the rendering result
     */
    public function renderMultiActions()
    {
        if (!$this->multiActions) {
            return '';
        }
        if ($this->dataProvider->getCount() <= 0) {
            return '';
        }
        $items = [];
        foreach ($this->multiActions as $multiAction) {
            $items[] = Html::a($multiAction['label'], $multiAction['url'], [
                'type' => 'button',
                'title' => !empty($multiAction['title']) ? $multiAction['title'] : $multiAction['label'],
                'class' => 'btn btn-default btn-xs modal-remote-form',
                'data-grid' => $this->id,
            ]);
        }
        return Html::ul($items, ['encode' => false, 'class' => 'multi-actions']);
    }

    /**
     * Renders the summary text.
     */
    public function renderSummary()
    {
        $count = $this->dataProvider->getCount();
        if ($count <= 0) {
            return '';
        }
        $summaryOptions = $this->summaryOptions;
        $tag = ArrayHelper::remove($summaryOptions, 'tag', 'div');
        if (($pagination = $this->dataProvider->getPagination()) !== false) {
            $totalCount = $this->dataProvider->getTotalCount();
            $begin = $pagination->getPage() * $pagination->pageSize + 1;
            $end = $begin + $count - 1;
            if ($begin > $end) {
                $begin = $end;
            }
            $page = $pagination->getPage() + 1;
            $pageCount = $pagination->pageCount;
            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('app', 'Showing <b>{begin, number}-{end, number}</b> of <b>{totalCount, number}</b> {totalCount, plural, one{row} other{rows}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions);
            }
        } else {
            $begin = $page = $pageCount = 1;
            $end = $totalCount = $count;
            if (($summaryContent = $this->summary) === null) {
                return Html::tag($tag, Yii::t('app', 'Total <b>{count, number}</b> {count, plural, one{row} other{rows}}.', [
                    'begin' => $begin,
                    'end' => $end,
                    'count' => $count,
                    'totalCount' => $totalCount,
                    'page' => $page,
                    'pageCount' => $pageCount,
                ]), $summaryOptions);
            }
        }

        return Yii::$app->getI18n()->format($summaryContent, [
            'begin' => $begin,
            'end' => $end,
            'count' => $count,
            'totalCount' => $totalCount,
            'page' => $page,
            'pageCount' => $pageCount,
        ], Yii::$app->language);
    }

    /**
     * @param $defaultPageSize
     */
    public function setDefaultPageSize($defaultPageSize)
    {
        $this->_defaultPageSize = $defaultPageSize;
    }

    /**
     * @return int
     */
    public function getDefaultPageSize()
    {
        if ($this->_defaultPageSize) {
            return $this->_defaultPageSize;
        }
        $identity = Yii::$app->user->identity;
        if (!empty($_GET[$this->id . '-per-page'])) {
            $perPage = $_GET[$this->id . '-per-page'];
            $pageSize = $identity->page_size;
            $pageSize[$this->id] = $perPage;
            $identity->setEavAttribute('page_size', $pageSize);
            $this->_defaultPageSize = $perPage;
        } elseif (!empty($identity->page_size[$this->id])) {
            $this->_defaultPageSize = $identity->page_size[$this->id];
        } else {
            $this->_defaultPageSize = $identity->page_limit ? $identity->page_limit : 25;
        }
        return $this->_defaultPageSize;
    }

}