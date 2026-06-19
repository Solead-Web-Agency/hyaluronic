<?php
/**
 * 2007-2022 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2022 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__) . '/AdminEtsACController.php');

class AdminEtsACDashboardController extends AdminEtsACController
{
    public $type;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->show_form_cancel_button = false;
        $this->_redirect = false;
        $this->show_toolbar = false;
        $this->list_no_link = true;

        $this->type = Tools::getIsset('type') && ($type = Tools::getValue('type')) && Validate::isCleanHtml($type) ? trim($type) : 'email';
        $this->table = 'ets_abancart_tracking';
        $this->className = 'EtsAbancartTracking';
        $this->list_id = $this->table;

        parent::__construct();

        $this->addRowAction('vieworder');

        $this->allow_export = false;
        $this->_orderBy = 'o.id_order';
        $this->_orderWay = 'DESC';

        $this->_select = '
            o.id_order
            , IF(a.id_ets_abancart_reminder = -1, \'' . pSQL($this->l('Manually abandoned cart emails', 'AdminEtsACDashboardController')) . '\',acl.name) as `campaign_name`
            , CONCAT(LEFT(cus.`firstname`, 1), \' . \', cus.`lastname`) `customer`, c.id_cart
            , o.total_paid_tax_incl, a.date_add, osl.`name` AS `osname`, os.`color`
            , IF(o.id_order, 1, 0) badge_success
            , IF(o.id_order, 0, 1) badge_danger
        ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (a.id_ets_abancart_reminder = ar.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_lang` acl ON (acl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign) 
            LEFT JOIN `' . _DB_PREFIX_ . 'cart` c ON (c.id_cart = a.id_cart) 
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` cus ON (cus.id_customer = c.id_customer) 
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = c.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = o.`current_state`)
			LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int)$this->context->language->id . ')
        ';

        $this->_where = '
            AND o.id_cart > 0
            AND (a.id_ets_abancart_reminder > 0 AND ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR a.id_ets_abancart_reminder = -1)  
            AND os.paid = 1
            AND a.delivered=1 
        ';

        $this->_group = 'GROUP BY o.id_order';

        if (Shop::getContext() !== Shop::CONTEXT_ALL)
            $this->_where .= ' AND a.id_shop = ' . (int)$this->context->shop->id;

        $statuses_array = array();
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('Order ID', 'AdminEtsACDashboardController'),
                'type' => 'int',
                'filter_key' => 'o!id_order',
                'orderby' => false,
                'search' => false,
                'class' => 'fixed-width-xs center',
            ),
            'campaign_name' => array(
                'title' => $this->l('Campaign name', 'AdminEtsACDashboardController'),
                'type' => 'text',
                'class' => 'center',
                'orderby' => false,
                'search' => false,
            ),
            'customer' => array(
                'title' => $this->l('Customer name', 'AdminEtsACDashboardController'),
                'type' => 'text',
                'align' => 'center',
                'orderby' => false,
                'search' => false,
            ),
            'id_cart' => array(
                'title' => $this->l('Products', 'AdminEtsACDashboardController'),
                'type' => 'price',
                'callback' => 'getOrderTotalUsingTaxCalculationMethod',
                'align' => 'center',
                'badge_danger' => true,
                'orderby' => false,
                'search' => false,
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('Order total', 'AdminEtsACDashboardController'),
                'type' => 'price',
                'callback' => 'displayPriceMethod',
                'align' => 'center',
                'badge_success' => true,
                'orderby' => false,
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date', 'AdminEtsACDashboardController'),
                'type' => 'datetime',
                'align' => 'center',
                'filter_key' => 'a!date_add',
                'class' => 'fixed-width-lg',
                'orderby' => false,
                'search' => false,
            ),
            'osname' => array(
                'title' => $this->l('Status', 'AdminEtsACDashboardController'),
                'type' => 'select',
                'color' => 'color',
                'align' => 'center',
                'list' => $statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname',
                'orderby' => false,
                'search' => false,
            ),
        );

        if ($this->type == 'leave') {
            unset($this->fields_list['campaign_name']);
        }
    }

    protected function checkOrderBy($orderBy)
    {
        if (empty($orderBy)) {
            $prefix = $this->getCookieFilterPrefix();

            if ($this->context->cookie->{$prefix . $this->list_id . 'Orderby'}) {
                $orderBy = $this->context->cookie->{$prefix . $this->list_id . 'Orderby'};
            } elseif ($this->_orderBy) {
                $orderBy = $this->_orderBy;
            } else {
                $orderBy = $this->_defaultOrderBy;
            }
        }

        /* Check params validity */
        if (!isset($this->fields_list[$orderBy]['order_key']) && isset($this->fields_list[$orderBy]['filter_key'])) {
            $this->fields_list[$orderBy]['order_key'] = $this->fields_list[$orderBy]['filter_key'];
        }

        if (isset($this->fields_list[$orderBy]['order_key'])) {
            $orderBy = $this->fields_list[$orderBy]['order_key'];
        }

        return $orderBy;
    }

    public function setHelperDisplay(Helper $helper)
    {
        parent::setHelperDisplay($helper);
        $helper->title = null;
    }

    public function initToolbar()
    {
    }

    public function initProcess()
    {
        parent::initProcess();

        if (null == $this->display) {
            $this->display = 'list';
        }
    }

    public function initContent()
    {
        parent::initContent();

        if ($this->display == 'list')
            $this->initBlock();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJS(array(
            $this->mPath . 'views/js/chart.admin.js',
        ));
    }

    public function ajaxProcessInitDashboard()
    {
        $this->initBlock();
    }

    static $cache_time_series;

    public function seriesFilter($time_series, $min_axes_x = 0, $max_axes_x = 0, $select_year = 0, $select_month = 0, $select_day = 0)
    {
        if (count($this->errors))
            return false;
        if (!$select_year) {
            $select_year = (int)date('Y');
        }
        if (!$select_month) {
            $select_month = (int)date('m');
        }
        if (!$select_day) {
            $select_day = (int)date('d');
        }
        $start = null;
        $end = null;
        $filter = [0 => null, 1 => null];

        switch ($time_series) {
            case 'all':
                $min = $min_axes_x ?: (int)EtsAbancartTools::getMinYear();
                $max = $max_axes_x ?: (int)$select_year;
                $filter[0] = ' YEAR(a.date_add) BETWEEN ' . (int)$min . ' AND ' . (int)$max;
                $filter[1] = ' a.`year` BETWEEN ' . (int)$min . ' AND ' . (int)$max;
                break;
            case 'last_year':
            case 'this_year':
                if (trim($time_series) !== 'this_year') {
                    $select_year = --$select_year;
                }
                $filter[0] = ' YEAR(a.date_add) = ' . (int)$select_year . ' AND MONTH(a.date_add) BETWEEN ' . (int)($min_axes_x ?: 1) . ' AND ' . (int)($max_axes_x ?: 12);
                $filter[1] = ' a.`year` = ' . (int)$select_year . ' AND a.`month` BETWEEN ' . (int)($min_axes_x ?: 1) . ' AND ' . (int)($max_axes_x ?: 12);
                break;
            case 'last_month':
            case 'this_month':
                $last_month_of_year = (int)date('t');
                if (trim($time_series) !== 'this_month') {
                    $timestamp = strtotime('-1 month');
                    $select_year = (int)date('Y', $timestamp);
                    $select_month = (int)date('m', $timestamp);
                    $last_month_of_year = (int)date('t', $timestamp);
                }
                $filter[0] = ' YEAR(a.date_add) = ' . (int)$select_year . ' AND MONTH(a.date_add) = ' . (int)$select_month . ' AND DAY(a.date_add) BETWEEN ' . (int)($min_axes_x ?: 1) . ' AND ' . (int)($max_axes_x ?: $last_month_of_year);
                $filter[1] = ' a.`year` = ' . (int)$select_year . ' AND a.`month` = ' . (int)$select_month . ' AND a.`day` BETWEEN ' . (int)($min_axes_x ?: 1) . ' AND ' . (int)($max_axes_x ?: $last_month_of_year);
                break;
            case 'yesterday':
            case 'today':
                if (trim($time_series) !== 'today') {
                    $timestamp = strtotime('-1 days');
                    $select_year = (int)date('Y', $timestamp);
                    $select_month = (int)date('m', $timestamp);
                    $select_day = (int)date('d', $timestamp);
                }
                $filter[0] = ' YEAR(a.date_add) = ' . (int)$select_year . ' AND MONTH(a.date_add) = ' . (int)$select_month . ' AND DAY(a.date_add) = ' . (int)$select_day . ' AND HOUR(a.date_add) BETWEEN ' . (int)($min_axes_x ?: 0) . ' AND ' . (int)($max_axes_x ?: 23);
                $filter[1] = ' a.`year` = ' . (int)$select_year . ' AND a.`month` = ' . (int)$select_month . ' AND a.`day` = ' . (int)$select_day;
                break;
            case 'time_range' :
                EtsAbancartReminderForm::getInstance()->validateTimeRange($start, $end, $this->errors);
                if (!$this->errors) {
                    if ((int)date('Y', strtotime($start)) != ($year = (int)date('Y', strtotime($end)))) {
                        return $this->seriesFilter('all', (int)date('Y', strtotime($start)), $year);
                    } else {
                        if ((int)date('m', strtotime($start)) != ($month = (int)date('m', strtotime($end)))) {
                            return $this->seriesFilter('this_year', (int)date('m', strtotime($start)), $month, $year);
                        } elseif ((int)date('d', strtotime($start)) != ($day = (int)date('d', strtotime($end)))) {
                            return $this->seriesFilter('this_month', (int)date('d', strtotime($start)), $day, $year, $month);
                        } else {
                            return $this->seriesFilter('today', (int)date('H', strtotime($start)), (int)date('H', strtotime($end)), $year, $month, $day);
                        }
                    }
                }
                break;
        }

        return !count($this->errors) ? $filter : false;
    }

    public function topStats($time_series, $min_axes_x = 0, $max_axes_x = 0, $select_year = 0, $select_month = 0, $select_day = 0)
    {
        $filter = $this->seriesFilter($time_series, $min_axes_x, $max_axes_x, $select_year, $select_month, $select_day);
        $currency = Currency::getDefaultCurrency();
        /*---Recovered Cart---*/
        $totalRecoveredCart = empty($filter[0]) ? 0 : Tools::displayPrice(EtsAbancartTools::getTotalOrderByIdShop($this->context->shop->id, $filter[0]), $currency, false);
        $countRecoveredCart = empty($filter[0]) ? 0 : EtsAbancartTools::getNbOrderByIdShop($this->context->shop->id, $filter[0]);
        /*---End Recovered Cart---*/

        /*---Abandoned Carts---*/
        $countAbandonedCart = empty($filter[0]) ? 0 : EtsAbancartTracking::getNbCartTracking($this->context->shop->id, $filter[0]);

        $totalAbandonedCart = empty($filter[0]) ? 0 : Tools::displayPrice(EtsAbancartTracking::getAbancartValue($this->context->shop->id, $filter[0]), $currency);
        /*---End Abandoned Carts---*/

        $totalGeneratedCode = (empty($filter[0]) ? 0 : EtsAbancartTracking::getNbGeneratedCode($this->context->shop->id, $filter[0])) + (empty($filter[1]) ? 0 : EtsAbancartDisplayTracking::getNbGeneratedCode($this->context->shop->id, $filter[1]));
        $totalUseGeneratedCode = (empty($filter[0]) ? 0 : EtsAbancartTracking::getNbGeneratedCodeUsed($this->context->shop->id, $filter[0])) + (empty($filter[1]) ? 0 : EtsAbancartDisplayTracking::getNbGeneratedCodeUsed($this->context->shop->id, $filter[1]));
        /*---End Generated Discount code---*/

        /*---Email reminders---*/

        $totalCampaigns = empty($filter[0]) ? 0 : EtsAbancartTracking::getTotalCampaigns($this->context->shop->id, $filter[0]);
        $totalEmailReminders = empty($filter[0]) ? 0 : EtsAbancartTracking::getTotalEmailReminders($this->context->shop->id, $filter[0]);
        /*---End Email reminders---*/

        return array(
            'rec' => array(
                'name' => $this->l('Recovered carts', 'AdminEtsACDashboardController'),
                'label' => sprintf($this->l('%s [1]from %d orders[/1]', 'AdminEtsACDashboardController'), $totalRecoveredCart, $countRecoveredCart),
                'icon' => 'icon-thumbs-up',
                'class' => 'recovered-cart',
                'desc' => $this->l('The revenue earned from recovered carts', 'AdminEtsACDashboardController'),
                'link' => [
                    'title' => $this->l('View recovered carts', 'AdminEtsACDashboardController'),
                    'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'ConvertedCarts', true)
                ],
            ),
            'aba' => array(
                'name' => $this->l('Abandoned cart value', 'AdminEtsACDashboardController'),
                'label' => sprintf($this->l('%s [1]from %d carts[/1]', 'AdminEtsACDashboardController'), $totalAbandonedCart, $countAbandonedCart),
                'icon' => 'icon-shopping-cart ',
                'class' => 'abandoned-cart',
                'desc' => $this->l('Total value from all abandoned carts', 'AdminEtsACDashboardController'),
                'link' => [
                    'title' => $this->l('View abandoned carts', 'AdminEtsACDashboardController'),
                    'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Cart', true)
                ],
            ),
            'gen' => array(
                'name' => $this->l('Generated Discount codes', 'AdminEtsACDashboardController'),
                'label' => sprintf($this->l('%d [1]used %d[/1]', 'AdminEtsACDashboardController'), $totalGeneratedCode, $totalUseGeneratedCode),
                'icon' => 'icon-tags',
                'class' => 'generated-voucher-code',
                'desc' => $this->l('The number of Discount codes generated for abandoned cart reminders and the number of Discount codes were used', 'AdminEtsACDashboardController'),
                'link' => [
                    'title' => $this->l('View discount codes', 'AdminEtsACDashboardController'),
                    'href' => $this->context->link->getAdminLink('AdminCartRules', true)
                ],
            ),
            'ema' => array(
                'name' => $this->l('Email reminders', 'AdminEtsACDashboardController'),
                'label' => sprintf($this->l('%d [1]from %d email campaigns[/1]', 'AdminEtsACDashboardController'), $totalEmailReminders, $totalCampaigns),
                'icon' => 'icon-envelope-o',
                'class' => 'create-reminder',
                'desc' => $this->l('The number of emails sent by email reminder campaigns', 'AdminEtsACDashboardController'),
                'link' => [
                    'title' => $this->l('View campaigns', 'AdminEtsACDashboardController'),
                    'href' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'ReminderEmail', true)
                ],
            ),
        );
    }

    public function initBlock()
    {
        if (!self::$cache_time_series) {
            self::$cache_time_series = array(
                'all' => array(
                    'label' => $this->l('All', 'AdminEtsACDashboardController'),
                ),
                'this_year' => array(
                    'label' => $this->l('This year', 'AdminEtsACDashboardController'),
                    'default' => 1,
                ),
                'last_year' => array(
                    'label' => $this->l('Last year', 'AdminEtsACDashboardController'),
                ),
                'this_month' => array(
                    'label' => $this->l('This month', 'AdminEtsACDashboardController'),
                ),
                'last_month' => array(
                    'label' => $this->l('Last month', 'AdminEtsACDashboardController'),
                ),
                'today' => array(
                    'label' => $this->l('Today', 'AdminEtsACDashboardController'),
                ),
                'yesterday' => array(
                    'label' => $this->l('Yesterday', 'AdminEtsACDashboardController'),
                ),
                'time_range' => array(
                    'label' => $this->l('Time range', 'AdminEtsACDashboardController'),
                ),
            );
        }

        $time_series = trim(Tools::getValue('time_series'));
        if ($time_series === '' || !isset(self::$cache_time_series[$time_series]) || !self::$cache_time_series[$time_series]) {
            $time_series = !isset($this->context->cookie->ets_abancart_time_series) || !$this->context->cookie->ets_abancart_time_series ? 'this_year' : $this->context->cookie->ets_abancart_time_series;
        }
        $this->context->cookie->ets_abancart_time_series = $time_series;
        $this->context->cookie->write();
        $this->context->smarty->assign([
            'campaigns_type' => EtsAbancartDefines::getInstance()->getFields('sub_menus'),
            'tracking_link' => $this->context->link->getAdminLink(Ets_abandonedcart::$slugTab . 'Tracking', true),
        ]);
        $tpl_vars = [
            'stats' => $this->trackingStats($time_series),
            'top_stats' => $this->topStats($time_series),
            'line_chart' => $this->lineChart($time_series),
        ];
        if ($this->ajax)
            die(json_encode($this->errors ? ['errors' => implode(Tools::nl2br(PHP_EOL), $this->errors)] : $tpl_vars));

        $tpl_vars['time_series_selected'] = $time_series;
        $tpl_vars['time_series'] = self::$cache_time_series;
        $tpl_vars['time_series_range'] = isset($this->context->cookie->ets_abancart_time_series_range) && $this->context->cookie->ets_abancart_time_series_range ? @json_decode($this->context->cookie->ets_abancart_time_series_range, true) : [];

        if (empty($tpl_vars['time_series_range'][0]) || empty($tpl_vars['time_series_range'][1])) {
            $time_series_range = [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('last day of this month'))];
            $tpl_vars['time_series_range'] = $time_series_range;
            $this->context->cookie->ets_abancart_time_series_range = @json_encode($time_series_range);
        }
        $this->ajaxProcessListRecent();
        $this->context->smarty->assign($tpl_vars);
        $this->content = $this->createTemplate('dashboard.tpl')->fetch();

        $this->renderAdmin();
    }

    public function ajaxProcessInitChart()
    {
        $chartType = trim(Tools::getValue('chartType'));
        $timeSeries = trim(($time_series = Tools::getValue('time_series'))) !== '' && Validate::isCleanHtml($time_series) ? $time_series : 'this_year';
        $json = [];
        switch ($chartType) {
            case 'line_chart':
                $id_campaign = trim(($id = Tools::getValue('id_campaign'))) !== '' && Validate::isUnsignedInt($id) ? $id : 'id_campaign';
                $json['line_chart'] = $this->lineChart($timeSeries, $id_campaign);
                break;
            case 'stats' :
                $json['stats'] = $this->trackingStats($timeSeries);
        }
        die(json_encode($json));
    }

    public function ajaxProcessListRecent()
    {
        $tpl_vars = array(
            'html' => $this->renderList()
        );
        if ($this->ajax)
            $this->toJson($tpl_vars);

        $this->context->smarty->assign($tpl_vars);
    }

    public function lineChart($time_series, $id_campaign = 0, $min_axes_x = 0, $max_axes_x = 0, $select_year = 0, $select_month = 0)
    {
        if (!count($this->errors)) {
            $reminder_filter = trim(Tools::getValue('reminder_filter'));
            if (trim($reminder_filter) === '' || !Validate::isCleanHtml($reminder_filter))
                $reminder_filter = 'recovered_carts';
            return EtsAbancartReminderForm::getInstance()->getLineChartCampaign($time_series, $id_campaign, $min_axes_x, $max_axes_x, $select_year, $select_month, $reminder_filter, $this->errors);
        }
    }

    public function trackingStats($time_series, $min_axes_x = 0, $max_axes_x = 0, $select_year = 0, $select_month = 0, $select_day = 0)
    {
        $filter = $this->seriesFilter($time_series, $min_axes_x, $max_axes_x, $select_year, $select_month, $select_day);
        if (!is_array($filter))
            return '';
        $abandonedCarts = array();
        if ($campaignList = EtsAbancartDefines::getInstance()->getFields('sub_menus')) {
            foreach ($campaignList as $campaign_type => $campaign) {
                if (trim($campaign_type) !== 'browser_tab') {
                    $abandonedCarts[$campaign_type] = array(
                        'title' => $campaign['label'],
                        'campaign_type' => $campaign_type,
                        'total_execute_times' => 0
                    );
                    if (in_array($campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER])) {
                        $abandonedCarts[$campaign_type] = array_merge($abandonedCarts[$campaign_type], [
                            'total_read' => 0,
                            'total_view' => 0,
                            'total_failed' => 0,
                            'total_success' => 0,
                            'total_queue' => 0,
                        ]);
                    }
                }
            }
        }
        $tpl_vars = array();
        if (!$this->errors) {
            if ($campaigns = EtsAbancartTracking::getDataTrackingCampaigns($filter[0], $this->context->shop->id)) {
                foreach ($campaigns as $campaign)
                    $abandonedCarts[$campaign['campaign_type']] = $campaign;
            }
            if ($campaigns = EtsAbancartDisplayTracking::getDataTrackingCampaigns($filter[1], $this->context->shop->id)) {
                foreach ($campaigns as $campaign)
                    $abandonedCarts[$campaign['campaign_type']] = $campaign;
            }
            $tpl_vars['abandoned_carts'] = $abandonedCarts;
        } else {
            $tpl_vars['errors'] = !$this->ajax ? $this->module->displayError($this->errors) : implode(PHP_EOL, $this->errors);
        }

        if ($this->ajax)
            $tpl_vars['campaigns_type'] = $campaignList;

        $this->context->smarty->assign($tpl_vars);

        return $this->createTemplate('stats.tpl')->fetch();
    }

    public function displayPriceMethod($price)
    {
        return Tools::displayPrice($price, Context::getContext()->currency);
    }

    public function displayViewOrderLink($token, $id, $name = null)
    {
        if (($tracking = new EtsAbancartTracking($id)) && $tracking->id_cart) {
            return $this->displayViewOrderLeaveLink($token, $tracking->id_cart, $name);
        }
    }

    public function displayViewOrderLeaveLink($token, $id, $name = null)
    {
        if ($id_order = EtsAbancartTools::getIdOrderByIdCart($id)) {
            if (!isset(self::$cache_lang['view_order'])) {
                self::$cache_lang['view_order'] = $this->l('View order', 'AdminEtsACDashboardController');
            }
            $this->context->smarty->assign(array(
                'href' => $this->context->link->getAdminLink('AdminOrders', true, version_compare(_PS_VERSION_, '1.7.7.0', '>=') ? ['route' => 'admin_orders_view', 'orderId' => (int)$id_order] : [], ['vieworder' => '', 'id_order' => (int)$id_order]),
                'action' => self::$cache_lang['view_order'],
            ));

            unset($token, $name);

            return $this->createTemplate('helpers/list/list_action_view_order.tpl')->fetch();

        }
    }

}