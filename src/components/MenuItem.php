<?php
/**
 * Created by PhpStorm.
 * User: Brett
 * Date: 17/02/2017
 * Time: 3:26 PM
 */

namespace app\components;

use app\components\ReturnUrl;
use cornernote\shortcuts\Y;
use Yii;
use yii\helpers\Html;


/**
 * MenuItem
 * @package app\components
 */
class MenuItem
{

    /**
     * @param string $until
     * @return string
     */
    public static function getNewLabel($until = 'tomorrow')
    {
        if (time() < strtotime($until)) {
            return ' <span class="label label-info">new</span>';
        }
        return '';
    }

    /**
     * @param $items
     * @return mixed
     */
    protected static function cleanItems($items)
    {
        foreach ($items as $k => $item) {
            $items[$k] = static::cleanItem($item);
            if (!$items[$k]) {
                unset($items[$k]);
            }
        }
        return $items;
    }

    /**
     * @param $item
     * @return mixed
     */
    public static function cleanItem($item)
    {
        if (isset($item['url']) && is_array($item['url'])) {
            $route = str_replace('/', '_', static::normalizeRoute($item['url'][0]));
            if (!Yii::$app->getModule(explode('_', $route)[0])) {
                $route = $route ? 'app_' . $route : 'app';
                //debug($route);
                $visible = isset($item['visible']) ? $item['visible'] : true;
                if ($visible) {
                    $user = Yii::$app->user;
                    if (!$user->can($route, ['route' => true]) && !$user->can($route . '_index', ['route' => true])) {
                        //$visible = false;
                        return false;
                    }
                } else {
                    return false;
                }
                //$item['visible'] = $visible;
            }
        }
        return $item;
    }

    /**
     * @param $route
     * @return string
     */
    protected static function normalizeRoute($route)
    {
        $route = Yii::getAlias((string)$route);
        if (strncmp($route, '/', 1) === 0) {
            return ltrim($route, '/');
        }
        if (Yii::$app->controller === null) {
            return '';
        }
        if (strpos($route, '/') === false) {
            return $route === '' ? Yii::$app->controller->getRoute() : Yii::$app->controller->getUniqueId() . '/' . $route;
        }
        return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
    }

    /**
     * @param bool $includeHistory
     * @return array
     */
    public static function getNavItems($includeHistory = true)
    {
        $items = [];
        $subItems = static::getDashboardsItems();
        if ($subItems) {
            $items[] = [
                //'label' => Yii::t('app', 'Dashboards'),
                'label' => '<span class="hidden-sm">' . Yii::t('app', 'Dashboards') . '</span>',
                //'url' => ['//dashboard'],
                'icon' => 'icon fa fa-tachometer',
                'items' => $subItems,
            ];
        }
        $subItems = static::getReportsItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('app', 'Reports') . '</span>',
                //'url' => ['//report'],
                'icon' => 'icon fa fa-area-chart',
                'items' => $subItems,
            ];
        }
        $subItems = static::getJobsItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('app', 'Jobs') . '</span>',
                //'url' => ['//job'],
                'icon' => 'icon fa fa-folder',
                'items' => $subItems,
            ];
        }
        $subItems = static::getCompaniesItems();
        if ($subItems) {
            $items[] = [
                'label' => '<span class="hidden-sm">' . Yii::t('app', 'Companies') . '</span>',
                //'url' => ['//company'],
                'icon' => 'icon fa fa-briefcase',
                'items' => $subItems,
            ];
        }
        if ($includeHistory) {
            $subItems = DynamicMenu::getMenuItems();
            if ($subItems) {
                $items[] = [
                    'icon' => 'icon fa fa-history',
                    'items' => $subItems,
                ];
            }
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getCustomItems()
    {
        $items = [];
        if (Yii::$app->user->can('goldoc_default_index', ['route' => true])) {
            $items[] = [
                //'label' => Yii::t('app', 'GOLDOC'),
                'url' => ['//goldoc'],
                'icon' => 'icon fa fa-trophy',
            ];
        }
        $subItems = static::cleanItems(static::getNotificationItems());
        if ($subItems) {
            $items[] = [
                'label' => '<span class="label label-warning">' . count($subItems) . '</span>',
                //'url' => ['//notifications'],
                'icon' => 'icon fa fa-bell-o',
                'items' => $subItems,
            ];
        }
        $subItems = static::getHelpItems();
        if ($subItems) {
            $items[] = [
                //'label' => Yii::t('app', 'Help'),
                //'url' => ['//help'],
                'icon' => 'icon fa fa-question-circle-o',
                'items' => $subItems,
            ];
        }
        $subItems = static::cleanItems(static::getSettingsItems());
        if ($subItems) {
            $items[] = [
                //'label' => Yii::t('app', 'Settings'),
                //'url' => ['//component'],
                'icon' => 'icon fa fa-wrench',
                'items' => $subItems,
            ];
        }
        /** @var \app\models\User $identity */
        $subItems = static::getUserItems();
        if ($subItems) {
            $identity = Y::user()->identity;
            $items[] = [
                'label' => $identity->getAvatar(25, ['class' => 'user-image']) . ' ' . $identity->username,
                'encode' => false,
                'items' => $subItems,
                'options' => ['class' => 'user user-menu'],
            ];
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getDashboardsItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['dashboard']) ? $_GET['dashboard'] : ''; //$c->action;
        $items = [];
        //$items[] = [
        //    'label' => Yii::t('app', 'CSR v3 Copy') . static::getNewLabel('2017-03-05'),
        //    'url' => ['//dashboard/csr-v3-copy'],
        //    'active' => ($c == 'dashboard' && $a == 'csr-v3-copy')
        //];
        $items[] = [
            'label' => Yii::t('app', 'Production'),
            'url' => ['//dashboard/production'],
            'active' => ($c == 'dashboard' && $a == 'production')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Coordination'),
            'url' => ['//dashboard/coordination'],
            'active' => ($c == 'dashboard' && $a == 'coordination')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Prepress'),
            'url' => ['//dashboard/prepress'],
            'active' => ($c == 'dashboard' && $a == 'prepress')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Printer'),
            'url' => ['//dashboard/printer'],
            'active' => ($c == 'dashboard' && in_array($a, ['printer', 'printer-1', 'printer-2'])),
            'items' => static::getDashboardPrinterItems(),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Sewing'),
            'url' => ['//dashboard/sewing'],
            'active' => ($c == 'dashboard' && $a == 'sewing')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Fabrication'),
            'url' => ['//dashboard/fabrication'],
            'active' => ($c == 'dashboard' && $a == 'fabrication')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Despatch'),
            'url' => ['//dashboard/despatch'],
            'active' => ($c == 'dashboard' && $a == 'despatch')
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getDashboardPrinterItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['dashboard']) ? $_GET['dashboard'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Printer'),
            'url' => ['//dashboard/printer'],
            'active' => ($c == 'dashboard' && $a == 'printer')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Printer 1'),
            'url' => ['//dashboard/printer-1'],
            'active' => ($c == 'dashboard' && $a == 'printer-1')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Printer 2'),
            'url' => ['//dashboard/printer-2'],
            'active' => ($c == 'dashboard' && $a == 'printer-2')
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getJobsItems()
    {
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Create Job'),
            'url' => ['//job/create'],
            'icon' => 'icon fa fa-plus',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Jobs'),
            'url' => ['//job'],
            'icon' => 'icon fa fa-folder',
            'items' => [
                [
                    'label' => Yii::t('app', 'Product Create'),
                    'url' => ['//product/create'],
                    'visible' => 0,
                ],
            ],
        ];
        //if (Y::user()->can('staff')) {
        //$items [] = [
        //    'label' => Yii::t('app', 'Deals') . ' <small>@HS</small>',
        //    'url' => 'https://app.hubspot.com/sales/2659477/deals/list/view/all/',
        //    'icon' => 'icon fa fa-external-link',
        //    'linkOptions' => ['target' => '_blank'],
        //];
        //}
        $items[] = [
            'label' => Yii::t('app', 'Rollouts'),
            'url' => ['//rollout'],
            'icon' => 'icon fa fa-object-group',
        ];
        $items[] = [
            'label' => '<hr style="margin:0;">',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Products'),
            'url' => ['//product'],
            'icon' => 'icon fa fa-tags',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Items'),
            'url' => ['//item'],
            'icon' => 'icon fa fa-tag',
        ];
        $items[] = [
            'label' => '<hr style="margin:0;">',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Packages'),
            'url' => ['//package'],
            'icon' => 'icon fa fa-archive',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Pickups'),
            'url' => ['//pickup'],
            'icon' => 'icon fa fa-truck',
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getCompaniesItems()
    {
        $items = [];
        $items [] = [
            'label' => Yii::t('app', 'Companies'),
            'url' => ['//company'],
            'icon' => 'icon fa fa-briefcase',
        ];
        //if (Y::user()->can('staff')) {
        //    $items [] = [
        //        'label' => Yii::t('app', 'Companies') . ' <small>@HS</small>',
        //        'url' => 'https://app.hubspot.com/sales/2659477/companies/list/view/all/',
        //        'icon' => 'icon fa fa-external-link',
        //        'linkOptions' => ['target' => '_blank'],
        //    ];
        //}
        $items [] = [
            'label' => Yii::t('app', 'Contacts'),
            'url' => ['//contact'],
            'icon' => 'icon fa fa-group',
        ];
        //if (Y::user()->can('staff')) {
        //    $items [] = [
        //        'label' => Yii::t('app', 'Contacts') . ' <small>@HS</small>',
        //        'url' => 'https://app.hubspot.com/sales/2659477/contacts/list/view/all/',
        //        'icon' => 'icon fa fa-external-link',
        //        'linkOptions' => ['target' => '_blank'],
        //    ];
        //}
        $items[] = [
            'label' => Yii::t('app', 'Staff List') . static::getNewLabel('2017-04-02'),
            'url' => ['//site/staff'],
            'icon' => 'icon fa fa-user-circle',
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getSettingsItems()
    {
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Audit'),
            'url' => ['//audit'],
            //'icon' => 'icon fa fa-refresh',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Carriers'),
            'url' => ['//carrier'],
            //'icon' => 'icon fa fa-cube',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Components'),
            'url' => ['//component'],
            //'icon' => 'icon fa fa-cube',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Component Types'),
            'url' => ['//component-type'],
            //'icon' => 'icon fa fa-cubes',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Item Types'),
            'url' => ['//item-type'],
            //'icon' => 'icon fa fa-object-ungroup',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Options'),
            'url' => ['//option'],
            //'icon' => 'icon fa fa-list',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Package Types'),
            'url' => ['//package-type'],
            //'icon' => 'icon fa fa-cube',
        ];
        $items [] = [
            'label' => Yii::t('app', 'Product Types'),
            'url' => ['//product-type'],
            //'icon' => 'icon fa fa-object-group',
            'items' => [
                [
                    'label' => Yii::t('app', 'Product Type To Item Types'),
                    'url' => ['//product-type-to-item-type'],
                    'visible' => false,
                ],
                [
                    'label' => Yii::t('app', 'Product Type To Options'),
                    'url' => ['//product-type-to-option'],
                    'visible' => false,
                ],
                [
                    'label' => Yii::t('app', 'Product Type To Component'),
                    'url' => ['//product-type-to-component'],
                    'visible' => false,
                ],
            ],
        ];
        $items [] = [
            'label' => Yii::t('app', 'Sizes'),
            'url' => ['//size'],
            //'icon' => 'icon fa fa-arrows',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Settings'),
            'url' => ['//settings/default'],
            //'icon' => 'icon fa fa-gears',
        ];
        if (Yii::$app->user->can('manager')) {
            $items [] = [
                'label' => Yii::t('app', 'Site Notes'),
                'url' => ['//note/create', 'ru' => ReturnUrl::getToken()],
                //'icon' => 'icon fa fa-info',
            ];
        }
        $items[] = [
            'label' => Yii::t('app', 'Users'),
            'url' => ['//user/admin'],
            //'icon' => 'icon fa fa-user',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Workflow'),
            'url' => ['//workflow'],
            //'icon' => 'icon fa fa-refresh',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Test/System Print'),
            'url' => ['/print-spool/test'],
            //'icon' => 'icon fa fa-refresh',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Test NPS'),
            'url' => ['/feedback/test'],
            //'icon' => 'icon fa fa-refresh',
        ];
        $items[] = [
            'label' => Yii::t('app', 'Clear Cache'),
            'url' => ['/site/clear-cache', 'ru' => ReturnUrl::getToken()],
            //'icon' => 'icon fa fa-refresh',
        ];
        if (YII_ENV == 'dev') {
            $items[] = [
                'label' => Yii::t('app', 'Backend'),
                'url' => ['//backend'],
                //'icon' => 'icon fa fa-cogs',
            ];
            $items[] = [
                'label' => Yii::t('app', 'Gii'),
                'url' => ['//gii'],
                //'icon' => 'icon fa fa-bolt',
            ];
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getNotificationItems()
    {
        $items = [];
        //$items[] = [
        //    'label' => Yii::t('app', 'Just a test notification...'),
        //    'url' => ['//help/test'],
        //];
        return static::cleanItems($items);
    }

    /**
     * @param mixed $page
     * @return string
     */
    public static function getWikiPageUrl($page = null)
    {
        if ($page === null) {
            $namespace = 'console';
            $module = (Yii::$app->module ? Yii::$app->module->id : Yii::$app->id);
            $controller = Yii::$app->controller->id;
            $action = Yii::$app->controller->action->id;
            if (in_array($controller, ['dashboard', 'report', 'help'])) {
                $action = Yii::$app->request->get($controller) ?: 'index';
            }
            $page = $namespace . ':' . $module . '_' . $controller . '_' . $action;
        }
        return 'https://afiwiki:wikiinside@wiki.afi.ink/' . $page;
    }

    /**
     * @return array
     */
    public static function getHelpItems()
    {
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Page Help'),
            'url' => static::getWikiPageUrl(),
            'linkOptions' => [
                'target' => '_blank',
            ],
        ];
        $items[] = [
            'label' => Yii::t('app', 'AFI Wiki') . static::getNewLabel('2017-03-14'),
            'url' => static::getWikiPageUrl(false),
            'linkOptions' => [
                'target' => '_blank',
            ],
        ];
        //$items[] = [
        //    'label' => Yii::t('app', 'Request Support'),
        //    'url' => ['//site/support'],
        //];
        $items[] = [
            'label' => Yii::t('app', 'Destroy this Page'),
            'url' => "javascript:var KICKASSVERSION='2.0';var s = document.createElement('script');s.type='text/javascript';document.body.appendChild(s);s.src='//hi.kickassapp.com/kickass.js';void(0);",
        ];
        if (Y::user()->can('admin')) {
            $items[] = [
                'label' => Yii::t('app', 'Developer Docs'),
                'url' => ['//docs'],
            ];
        }
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getReportsItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Finance Todo'),
            'url' => ['//report/finance-todo'],
            'active' => ($c == 'report' && $a == 'finance-todo')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Sales Pipeline'),
            'url' => ['//report/sales-pipeline'],
            'active' => ($c == 'report' && $a == 'sales-pipeline')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Sales Manager'),
            'url' => ['//report/sales-manager'],
            'active' => ($c == 'report' && $a == 'sales-manager')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Finance Pipeline') . static::getNewLabel('2017-06-05'),
            'url' => ['//report/finance-pipeline'],
            'active' => ($c == 'report' && $a == 'finance-pipeline')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Net Promoter Score'),
            'url' => ['//report/feedback'],
            'active' => ($c == 'report' && in_array($a, ['feedback', 'feedback-contacts', 'feedback-sent'])),
            'items' => static::getReportFeedbackItems(),
        ];
        $items[] = [
            'label' => Yii::t('app', 'New Companies'),
            'url' => ['//report/new-companies'],
            'active' => ($c == 'report' && $a == 'new-companies'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Company Trading Time') . static::getNewLabel('2017-07-14'),
            'url' => ['//report/company-trading-time'],
            'active' => ($c == 'report' && $a == 'company-trading-time')
        ];
        $items[] = [
            'label' => Yii::t('app', 'Quotes'),
            'url' => ['//report/quotes'],
            'active' => ($c == 'report' && $a == 'quotes'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Sales'),
            'url' => ['//report/sales'],
            'active' => ($c == 'report' && in_array($a, ['sales', 'company-sales', 'product-sales'])),
            'items' => static::getReportSalesItems(),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Profit'),
            'url' => ['//report/profit'],
            'active' => ($c == 'report' && in_array($a, ['rep-profit', 'company-profit', 'product-profit'])),
            'items' => static::getReportProfitItems(),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Performance'),
            'url' => ['//report/performance'],
            'active' => ($c == 'report' && in_array($a, ['performance', 'sales-performance', 'rep-performance', 'company-performance', 'product-performance'])),
            'items' => static::getReportPerformanceItems(),
        ];
        $items[] = [
            'label' => Yii::t('app', 'ReDos'),
            'url' => ['//report/performance'],
            'active' => ($c == 'report' && in_array($a, ['redos', 'company-redos', 'product-redos'])),
            'items' => static::getReportRedosItems(),
        ];
        //$items[] = [
        //    'label' => Yii::t('app', 'Quote Compare'),
        //    'url' => ['//report/quote-compare'],
        //    'active' => ($c == 'report' && $a == 'quote-compare'),
        //];
        $items[] = [
            'label' => Yii::t('app', 'Info'),
            'url' => ['//report/info'],
            'active' => ($c == 'report' && in_array($a, ['info', 'quote-class', 'product-type', 'permission'])),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Check'),
            'url' => ['//report/check'],
            'active' => ($c == 'report' && in_array($a, ['check', 'company-check', 'company-domain-check', 'contact-check', 'rep-check', 'hub-spot-check'])),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Workflow Info') . static::getNewLabel('2017-07-01'),
            'url' => ['//report/workflow'],
            'active' => ($c == 'report' && $a == 'workflow')
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getReportFeedbackItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'NPS Report'),
            'url' => ['//report/feedback'],
            'active' => ($c == 'report' && $a == 'feedback'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'NPS Contacts'),
            'url' => ['//report/feedback-contacts'],
            'active' => ($c == 'report' && $a == 'feedback-contacts'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'NPS Sent'),
            'url' => ['//report/feedback-sent'],
            'active' => ($c == 'report' && $a == 'feedback-sent'),
        ];
        return $items;
    }

    /**
     * @return array
     */
    public static function getReportSalesItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Sales'),
            'url' => ['//report/sales'],
            'active' => ($c == 'report' && $a == 'sales'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Company Sales'),
            'url' => ['//report/company-sales'],
            'active' => ($c == 'report' && $a == 'company-sales'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Product Sales'),
            'url' => ['//report/product-sales'],
            'active' => ($c == 'report' && $a == 'product-sales'),
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getReportRedosItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'ReDos'),
            'url' => ['//report/redos'],
            'active' => ($c == 'report' && $a == 'redos'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Company ReDos'),
            'url' => ['//report/company-redos'],
            'active' => ($c == 'report' && $a == 'company-redos'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Product ReDos'),
            'url' => ['//report/product-redos'],
            'active' => ($c == 'report' && $a == 'product-redos'),
        ];
        return static::cleanItems($items);
    }

    public static function getReportProfitItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Rep Profit'),
            'url' => ['//report/rep-profit'],
            'active' => ($c == 'report' && $a == 'rep-profit'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Company Profit'),
            'url' => ['//report/company-profit'],
            'active' => ($c == 'report' && $a == 'company-profit'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Product Profit'),
            'url' => ['//report/product-profit'],
            'active' => ($c == 'report' && $a == 'product-profit'),
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getReportPerformanceItems()
    {
        $c = Yii::$app->controller->id;
        $a = isset($_GET['report']) ? $_GET['report'] : ''; //$c->action;
        $items = [];
        $items[] = [
            'label' => Yii::t('app', 'Sales Performance'),
            'url' => ['//report/sales-performance'],
            'active' => ($c == 'report' && $a == 'sales-performance'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Rep Performance'),
            'url' => ['//report/rep-performance'],
            'active' => ($c == 'report' && $a == 'rep-performance'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Company Performance'),
            'url' => ['//report/company-performance'],
            'active' => ($c == 'report' && $a == 'company-performance'),
        ];
        $items[] = [
            'label' => Yii::t('app', 'Product Performance'),
            'url' => ['//report/product-performance'],
            'active' => ($c == 'report' && $a == 'product-performance'),
        ];
        return static::cleanItems($items);
    }

    /**
     * @return array
     */
    public static function getUserItems()
    {
        $user = Yii::$app->user;
        /** @var \app\models\User $identity */
        $identity = $user->identity;
        $items = [];
        $items[] = [
            'label' => $identity->getAvatar(90, ['class' => 'img-circle']) . '<p>' . $identity->label . ' [' . $identity->username . ']<small>' . $identity->email . '</small></p>',
            'options' => ['class' => 'user-header'],
        ];
        $items[] = [
            'label' => implode('', [
                Html::tag('div', Html::a(Yii::t('app', 'Settings'), ['//user/settings/application'], ['class' => 'btn btn-default btn-flat']), ['class' => 'pull-left']),
                Html::tag('div', Html::a(Yii::t('app', 'Sign out'), ['//user/security/logout'], ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']), ['class' => 'pull-right']),
            ]),
            'options' => ['class' => 'user-footer'],
        ];
        return $items;
    }
}