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

class EtsAbancartReminderForm extends EtsAbancartCore
{
    static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new EtsAbancartReminderForm();
        }
        return self::$instance;
    }

    static $reminder_menus;

    public function getReminderSteps()
    {
        if (!self::$reminder_menus) {
            self::$reminder_menus = array(
                'frequency' => array(
                    'label' => $this->l('Timing', 'EtsAbancartReminderForm'),
                    'origin' => 'Timing',
                    'icon' => 'icon-clock-o',
                    'object' => 'email,popup,bar,browser,customer',
                ),
                'discount' => array(
                    'label' => $this->l('Discount', 'EtsAbancartReminderForm'),
                    'origin' => 'Discount',
                    'icon' => 'icon-tag',
                ),

                'select_template' => array(
                    'label' => $this->l('Email template', 'EtsAbancartReminderForm'),
                    'origin' => 'Email template',
                    'icon' => 'icon-file-text-o',
                    'object' => 'email,cart,customer',
                ),
                'message' => array(
                    'label' => $this->l('Email content', 'EtsAbancartReminderForm'),
                    'origin' => 'Email content',
                    'icon' => 'icon icon-eye',
                    'reminder_type' => [
                        'popup' => $this->l('Popup content', 'EtsAbancartReminderForm'),
                        'bar' => $this->l('Highlight bar template', 'EtsAbancartReminderForm'),
                        'browser' => $this->l('Web push notification template', 'EtsAbancartReminderForm'),
                        'customer' => $this->l('Email', 'EtsAbancartReminderForm'),
                    ]
                ),
                'confirm_information' => array(
                    'label' => $this->l('Confirm information', 'EtsAbancartReminderForm'),
                    'origin' => 'Email content',
                    'icon' => 'icon icon-check-circle-o',
                    'object' => 'email,popup,bar,browser,customer',
                ),
            );
            if (!EtsAbancartForm::getTotalForm(true)) {
                unset(self::$reminder_menus['lead_form']);
            }
        }
        return self::$reminder_menus;
    }

    public function getCustomerEmailSendOptions()
    {
        return array(
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION,
                'name' => $this->l('After customer registration', 'EtsAbancartReminderForm')
            ),
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION,
                'name' => $this->l('After order completion', 'EtsAbancartReminderForm')
            ),
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME,
                'name' => $this->l('Schedule time', 'EtsAbancartReminderForm')
            ),
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW,
                'name' => $this->l('Run now', 'EtsAbancartReminderForm')
            ),
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER,
                'name' => $this->l('After subscribing newsletter', 'EtsAbancartReminderForm')
            ),
            array(
                'id' => EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN,
                'name' => $this->l('Last visit time', 'EtsAbancartReminderForm')
            ),
        );
    }

    public function getReminderStatusOptions($reminder_customer_option, $is_fields_list = false)
    {
        $status = [
            [
                'id' => EtsAbancartReminder::REMINDER_STATUS_DRAFT,
                'name' => $this->l('Draft', 'EtsAbancartReminderForm'),
                'ref' => EtsAbancartReminder::REMINDER_STATUS_RUNNING,
            ]
        ];
        if ($reminder_customer_option != EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW) {
            $status = array_merge($status, [
                [
                    'id' => EtsAbancartReminder::REMINDER_STATUS_RUNNING,
                    'name' => $reminder_customer_option == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME ? $this->l('Pending', 'EtsAbancartReminderForm') : $this->l('Running', 'EtsAbancartReminderForm'),
                    'ref' => EtsAbancartReminder::REMINDER_STATUS_STOP,
                ],
                [
                    'id' => EtsAbancartReminder::REMINDER_STATUS_STOP,
                    'name' => $this->l('Stop', 'EtsAbancartReminderForm'),
                    'ref' => EtsAbancartReminder::REMINDER_STATUS_RUNNING,
                ],
            ]);
        }
        $status = array_merge($status, [
            [
                'id' => EtsAbancartReminder::REMINDER_STATUS_FINISHED,
                'name' => $this->l('Finished', 'EtsAbancartReminderForm'),
                'ref' => 0
            ]
        ]);
        if ($is_fields_list) {
            $values = [];
            foreach ($status as $item) {
                $values[$item['id']] = $item['name'];
            }
            return $values;
        }

        return $status;
    }

    public function displayListProduct($input_name, $id_products = array(), $idList = null, $extraName = '', $showDeleteBtn = true)
    {

        if ($id_products) {
            $products = Db::getInstance()->executeS('
                SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, MAX(image_shop.`id_image`) id_image, il.`legend` 
                FROM `' . _DB_PREFIX_ . 'product` p
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.id_product=pl.id_product)
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
                Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$this->context->language->id . ')
                WHERE p.id_product IN (' . implode(',', array_map('intval', $id_products)) . ') 
                GROUP BY p.id_product
            ');
            if ($products) {
                if (version_compare(_PS_VERSION_, '1.7', '>='))
                    $type_image = ImageType::getFormattedName('home');
                else
                    $type_image = ImageType::getFormatedName('home');
                foreach ($products as &$product) {
                    $product['url_image'] = $product['id_image'] ? str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($product['link_rewrite'], $product['id_image'], $type_image)) : false;
                    $product['link_product'] = $this->context->link->getProductLink((int)$product['id_product'], $product['link_rewrite'], null, null, $this->context->language->id);
                    if ($extraName)
                        $product['name'] .= ' ' . $extraName;
                }
                if (isset($product)) {
                    unset($product);
                }
            }

        } else
            $products = array();
        $this->context->smarty->assign(
            array(
                'products' => $products,
                'input_name' => $input_name,
                'idList' => $idList,
                'showDeleteBtn' => $showDeleteBtn,
            )
        );
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/hook/product_list.tpl');
    }

    public function exportEmailSentToCsv($id_campaign, $time, $dateFrom, $dateTo)
    {
        $startDate = "";
        $endDate = "";
        switch ($time) {
            case 'this_year':
                $startDate = date('Y-01-01');
                $endDate = date('Y-m-d');
                break;
            case 'last_year':
                $startDate = (date('Y') - 1) . '-01-01';
                $endDate = (date('Y') - 1) . '-12-31';
                break;
            case 'this_month':
                $startDate = date('Y-m-01');
                $endDate = date('Y-m-t');
                break;
            case 'last_month':
                $startDate = date("Y-n-j", strtotime("first day of previous month"));
                $endDate = date("Y-n-j", strtotime("last day of previous month"));
                break;
            case 'today':
                $startDate = date('Y-m-d');
                $endDate = date('Y-m-d');
                break;
            case 'yesterday':
                $startDate = date('Y-m-d', strtotime('-1 day'));
                $endDate = date('Y-m-d', strtotime('-1 day'));
                break;
            case 'time_range':
                $startDate = $dateFrom;
                $endDate = $dateTo;
                break;
        }
        $dataExport = EtsAbancartCampaign::getEmailSent($id_campaign, 0, $startDate, $endDate, $this->context);
        $campaign = new EtsAbancartCampaign($id_campaign, Context::getContext()->language->id);
        $filename = $campaign->campaign_type . '_' . $campaign->name . '_' . date('d_m_Y') . ".csv";
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-type: application/x-msdownload");
        $flag = false;
        $csv = '';
        $fields = array(
            'id_ets_abancart_reminder' => $this->l('Reminder ID', 'EtsAbancartReminderForm'),
            'subject' => $this->l('Email subject', 'EtsAbancartReminderForm'),
            'ip_address' => $this->l('IP address', 'EtsAbancartReminderForm'),
            'id_customer' => $this->l('ID customer', 'EtsAbancartReminderForm'),
            'firstname' => $this->l('First name', 'EtsAbancartReminderForm'),
            'lastname' => $this->l('Last name', 'EtsAbancartReminderForm'),
            'email' => $this->l('Email', 'EtsAbancartReminderForm'),
            'date_add' => $this->l('Date', 'EtsAbancartReminderForm'),
        );
        if ($campaign->campaign_type == 'email' || $campaign->campaign_type == 'customer') {
            $fields['status'] = $this->l('Status', 'EtsAbancartReminderForm');
        }

        if ($dataExport) {
            foreach ($dataExport as $item) {
                if ($item['campaign_type'] == 'bar') {
                    $item['subject'] = $this->l('Highlight bar', 'EtsAbancartReminderForm');
                } elseif ($item['campaign_type'] == 'browser') {
                    $item['subject'] = $this->l('Web push notification', 'EtsAbancartReminderForm');
                } elseif ($item['campaign_type'] == 'leave') {
                    $item['subject'] = $this->l('Leaving website', 'EtsAbancartReminderForm');
                } else {
                    $item['subject'] = $item['title'];
                }

                $titles = array();
                $values = array();
                foreach ($fields as $key => $title) {
                    if (!$flag) {
                        $titles[] = $title;
                    }

                    if ($key == 'status') {
                        if ($item['delivered'] == 1) {
                            $values[] = $this->l('Sent successfully', 'EtsAbancartReminderForm');
                        } else {
                            $values[] = $this->l('Sending failed', 'EtsAbancartReminderForm');
                        }
                    } else
                        $values[] = (string)$item[$key];
                }
                if (!$flag) {
                    $csv .= join("\t", $titles) . "\r\n";
                    $flag = true;
                }

                $csv .= join("\t", $values) . "\r\n";
            }

            $csv = chr(255) . chr(254) . mb_convert_encoding($csv, "UTF-16LE", "UTF-8");
            echo $csv;
        } else {
            echo $this->l('No campaign data found', 'EtsAbancartReminderForm');
        }
        exit;
    }

    public function getLineChartCampaign($time_series, $id_campaign = 0, $min_axes_x = 0, $max_axes_x = 0, $select_year = 0, $select_month = 0, $reminder_filter = null, &$errors = null)
    {
        $errors = array();
        if (!$select_year)
            $select_year = (int)date('Y');
        if (!$select_month)
            $select_month = (int)date('m');

        $axes_x = $this->l('Years', 'EtsAbancartReminderForm');
        $data_axes_x = array();

        $campaign = new EtsAbancartCampaign($id_campaign);
        $isPopupTracking = $campaign->id > 0 && in_array($campaign->campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_POPUP, EtsAbancartCampaign::CAMPAIGN_TYPE_BAR, EtsAbancartCampaign::CAMPAIGN_TYPE_BROWSER, EtsAbancartCampaign::CAMPAIGN_TYPE_LEAVE]);
        $ticks = $isPopupTracking || $reminder_filter !== 'recovered_carts' ? 'int' : 'float';

        $sql_shop = ' AND ' . ($isPopupTracking || $reminder_filter !== 'recovered_carts' ? 't' : 'o') . '.id_shop = ' . (int)Context::getContext()->shop->id;
        $sql_order = !$isPopupTracking && $reminder_filter == 'recovered_carts' ? ' FROM `' . _DB_PREFIX_ . 'orders` o ' : '';
        $start = null;
        $end = null;
        $filterWhere = 'WHERE 1';

        if (!$isPopupTracking) {
            $sql_abandoned_cart = $reminder_filter !== 'recovered_carts' ? ' FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` t ' : '';
        } else
            $sql_abandoned_cart = ' FROM `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` t ';

        if ($campaign->id > 0 && $reminder_filter !== 'recovered_carts') {
            $filterWhere .= ' AND ar.id_ets_abancart_campaign=' . (int)$campaign->id;
            $sql_abandoned_cart .= ' LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = t.id_ets_abancart_reminder) ';
        }

        if ($campaign->id > 0 && trim($reminder_filter) == 'email_queue') {
            $sql_shop = ' AND eq.id_shop=' . (int)Context::getContext()->shop->id;
            $sql_abandoned_cart = '
                FROM  `' . _DB_PREFIX_ . 'ets_abancart_email_queue` eq  
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = eq.id_ets_abancart_reminder) 
            ';
        }
        if ($isPopupTracking)
            $select = 'SUM(t.number_of_displayed)';
        else
            $select = 'SUM(IF(' . ($campaign->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER ? 't.id_customer > 0' : 't.id_cart > 0') . ', ' . ($reminder_filter == 'recovered_carts' ? 'o.total_paid_tax_incl' : '1') . ', 0))';

        $yLabel = Currency::getDefaultCurrency()->iso_code;
        $xLabel = $this->l('Total from recovered carts', 'EtsAbancartReminderForm');
        $columnFilter = 't.display_times';

        switch (trim($reminder_filter)) {
            case 'recovered_carts':
                $sql_abandoned_cart .= '
                    JOIN (
                        SELECT t.id_cart
                        FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` t
                        LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = t.id_ets_abancart_reminder)
                        LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
                        WHERE t.delivered=1
                          AND t.id_cart > 0
                          AND (t.id_ets_abancart_reminder > 0 AND ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR t.id_ets_abancart_reminder = -1)
                        GROUP BY t.id_cart
                    ) `t` ON (o.id_cart = t.id_cart)
                    LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.id_order_state = o.current_state)
                ';
                $filterWhere .= ' AND os.paid = 1';
                $columnFilter = 'o.date_add';
                break;
            case 'abancart_mail_sent':
            case 'email_sent':
                $select = 'SUM(IF(t.`delivered`=1 AND ' . (trim($reminder_filter) == 'email_sent' && $campaign->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER ? 't.id_customer > 0' : 't.id_cart > 0') . ', 1, 0))';
                $yLabel = $this->l('Email(s)', 'EtsAbancartReminderForm');
                $xLabel = $this->l('Total email sent', 'EtsAbancartReminderForm');
                break;
            case 'email_fail':
                $select = 'SUM(IF(t.`delivered`=0, 1, 0))';
                $yLabel = $this->l('Email(s)', 'EtsAbancartReminderForm');
                $xLabel = $this->l('Total failed email', 'EtsAbancartReminderForm');
                break;
            case 'email_read':
                $select = 'SUM(IF(t.`read` = 1, 1, 0))';
                $yLabel = $this->l('Email(s)', 'EtsAbancartReminderForm');
                $xLabel = $this->l('Total read email', 'EtsAbancartReminderForm');
                break;
            case 'email_queue':
                $columnFilter = 'eq.date_add';
                $select = 'COUNT(`id_ets_abancart_email_queue`)';
                $yLabel = $this->l('Email(s)', 'EtsAbancartReminderForm');
                $xLabel = $this->l('Total email queue', 'EtsAbancartReminderForm');
                break;
            case 'all_reminders':
                $yLabel = $this->l('Display times', 'EtsAbancartReminderForm');
                $xLabel = $this->l('Reminder(s)', 'EtsAbancartReminderForm');
                break;
        }

        if ($isPopupTracking && $id_campaign > 0 && Validate::isUnsignedInt($reminder_filter)) {
            $filterWhere .= ' AND ar.id_ets_abancart_reminder=' . (int)$reminder_filter;
            $yLabel = $this->l('Display times', 'EtsAbancartReminderForm');
            $xLabel = $this->l('Reminder(s)', 'EtsAbancartReminderForm');
        }

        $select .= ' `total`';
        switch ($time_series) {
            case 'all':
                $min = $min_axes_x ?: (int)Db::getInstance()->getValue('SELECT YEAR(o.date_add) FROM `' . _DB_PREFIX_ . 'orders` o ORDER BY o.date_add ASC');
                $max = $max_axes_x ?: (int)$select_year;
                for ($year = $min; $year <= $max; $year++) {
                    $data_axes_x[$year] = $year;
                }
                if (count($data_axes_x) < 2) {
                    $data_axes_x[($year2 = --$min)] = $year2;
                    ksort($data_axes_x, SORT_NUMERIC);
                }
                $select .= ', ' . ($isPopupTracking ? 't.year' : 'YEAR(' . pSQL($columnFilter) . ')') . ' `time`';
                $filterWhere .= $sql_shop;
                $sql_abandoned_cart = $sql_order . $sql_abandoned_cart . $filterWhere . ' GROUP BY `time` ';
                break;
            case 'last_year':
            case 'this_year':
                if (trim($time_series) !== 'this_year') {
                    $select_year = --$select_year;
                }
                $axes_x = $this->l('Months', 'EtsAbancartReminderForm');
                $months = Tools::dateMonths();
                foreach ($months as $month => $label) {
                    if ($min_axes_x && $max_axes_x && ($month >= $min_axes_x && $month <= $max_axes_x) || !$min_axes_x && !$max_axes_x) {
                        $data_axes_x[$month] = $label;
                    }
                }
                $select .= ', ' . ($isPopupTracking ? 't.month' : 'MONTH(' . pSQL($columnFilter) . ')') . ' `time`';
                $filterWhere .= ' AND ' . ($isPopupTracking ? 't.year' : 'YEAR(' . pSQL($columnFilter) . ')') . ' = ' . (int)$select_year . $sql_shop;
                $sql_abandoned_cart = $sql_order . $sql_abandoned_cart . $filterWhere . ' GROUP BY `time` ';
                break;
            case 'last_month':
            case 'this_month':
                if (trim($time_series) !== 'this_month') {
                    $timestamp = strtotime('-1 month');
                    $select_year = (int)date('Y', $timestamp);
                    $select_month = (int)date('m', $timestamp);
                }
                $axes_x = $this->l('Days', 'EtsAbancartReminderForm');
                $days = (int)date('t', mktime(0, 0, 0, (int)$select_month, 1, (int)$select_year));
                for ($day = 1; $day <= $days; $day++) {
                    if ($min_axes_x && $max_axes_x && ($day >= $min_axes_x && $day <= $max_axes_x) || !$min_axes_x && !$max_axes_x) {
                        $data_axes_x[$day] = $day;
                    }
                }
                $select .= ', ' . ($isPopupTracking ? 't.day' : 'DAY(' . pSQL($columnFilter) . ')') . ' `time`';
                $filterWhere .= ' AND ' . ($isPopupTracking ? 't.month' : 'MONTH(' . pSQL($columnFilter) . ')') . ' = ' . (int)$select_month . ' AND ' . ($isPopupTracking ? 't.year' : 'YEAR(' . pSQL($columnFilter) . ')') . ' = ' . (int)$select_year . $sql_shop;
                $sql_abandoned_cart = $sql_order . $sql_abandoned_cart . $filterWhere . ' GROUP BY `time` ';
                break;
            case 'today':
            case 'yesterday':
                if (trim($time_series) == 'yesterday') {
                    $timestamp = strtotime('-1 day');
                } else {
                    $timestamp = time();
                }
                $select_year = (int)date('Y', $timestamp);
                $select_month = (int)date('m', $timestamp);
                $axes_x = $this->l('Days', 'EtsAbancartReminderForm');
                $day = (int)date('d', $timestamp);
                if ($min_axes_x && $max_axes_x && ($day >= $min_axes_x && $day <= $max_axes_x) || !$min_axes_x && !$max_axes_x) {
                    $data_axes_x[$day] = $day;
                }
                if (count($data_axes_x) < 2) {
                    $data_axes_x[($day2 = date('d', $timestamp - 86400))] = $day2;
                    ksort($data_axes_x, SORT_NUMERIC);
                }
                $select .= ', ' . ($isPopupTracking ? 't.day' : 'DAY(' . pSQL($columnFilter) . ')') . ' `time`';
                $filterWhere .= ' AND ' . ($isPopupTracking ? 't.day' : 'DAY(' . pSQL($columnFilter) . ')') . ' = ' . (int)$day . ' AND ' . ($isPopupTracking ? 't.month' : 'MONTH(' . pSQL($columnFilter) . ')') . ' = ' . (int)$select_month . ' AND ' . ($isPopupTracking ? 't.year' : 'YEAR(' . pSQL($columnFilter) . ')') . ' = ' . (int)$select_year . $sql_shop;
                $sql_abandoned_cart = $sql_order . $sql_abandoned_cart . (string)$filterWhere . ' GROUP BY `time` ';
                break;
            case 'time_range' :
                $this->validateTimeRange($start, $end, $errors);
                if (!$errors) {
                    if ((int)date('Y', strtotime($start)) != ($year = (int)date('Y', strtotime($end)))) {
                        return $this->getLineChartCampaign('all', $id_campaign, (int)date('Y', strtotime($start)), (int)date('Y', strtotime($end)), 0, 0, $reminder_filter, $errors);
                    } else {
                        if ((int)date('m', strtotime($start)) != ($month = (int)date('m', strtotime($end)))) {
                            return $this->getLineChartCampaign('this_year', $id_campaign, (int)date('m', strtotime($start)), $month, $year, 0, $reminder_filter, $errors);
                        } else {
                            return $this->getLineChartCampaign('this_month', $id_campaign, (int)date('d', strtotime($start)), (int)date('d', strtotime($end)), $year, $month, $reminder_filter, $errors);
                        }
                    }
                }
                break;
        }
        if (!$errors && count($data_axes_x) > 0) {
            $abandoned_carts = [];
            foreach (array_keys($data_axes_x) as $time)
                $abandoned_carts[$time] = ($ticks == 'int' ? 0 : 0.0);
            if ($seriesAbandonedCarts = Db::getInstance()->executeS('SELECT ' . $select . $sql_abandoned_cart))
                foreach ($seriesAbandonedCarts as $seriesAbandonedCart)
                    $abandoned_carts[(int)$seriesAbandonedCart['time']] = ($ticks == 'int' ? (int)$seriesAbandonedCart['total'] : (float)$seriesAbandonedCart['total']);

            $data = array_values($abandoned_carts);
            return array(
                'title' => $this->l('Line chart', 'EtsAbancartReminderForm'),
                'axesX' => $axes_x,
                'axesY' => $yLabel,
                'datasets' => array(
                    array(
                        'label' => $xLabel,
                        'data' => $data,
                        'backgroundColor' => '#f67019',
                        'borderColor' => '#f67019',
                        'borderWidth' => 1,
                        'fill' => false,
                    )
                ),
                'dataAxesX' => array_values($data_axes_x),
                'minY' => min($data),
                'maxY' => max($data),
            );
        }

        return [];
    }

    public function validateTimeRange(&$from_date, &$to_date, &$errors = [])
    {
        $time_series_range = isset($this->context->cookie->ets_abancart_time_series_range) ? @json_decode($this->context->cookie->ets_abancart_time_series_range, true) : [];
        $from_date = trim(Tools::getValue('from_time'));
        $to_date = trim(Tools::getValue('to_time'));

        if ($from_date == '' && Tools::getIsset('from_time') || (!Tools::getIsset('from_time') && (!isset($time_series_range[0]) || ($from_date = trim($time_series_range[0])) == ''))) {
            $errors[] = $this->l('"From" time is required', 'AdminEtsACDashboardController');
        } elseif (!Validate::isDate($from_date))
            $errors[] = $this->l('"From" time is invalid', 'AdminEtsACDashboardController');
        elseif ($to_date == '' && Tools::getIsset('to_time') || (!Tools::getIsset('to_time') && (!isset($time_series_range[1]) || ($to_date = trim($time_series_range[1])) == ''))) {
            $errors[] = $this->l('"To" time is required', 'AdminEtsACDashboardController');
        } elseif (!Validate::isDate($to_date))
            $errors[] = $this->l('"To" time is invalid', 'AdminEtsACDashboardController');

        $this->context->cookie->ets_abancart_time_series_range = @json_encode([$from_date, $to_date]);
        $this->context->cookie->write();
    }

    public function propertiesTracking($id_ets_abancart_reminder, $title, $id_cart_rule, $id_lang, $id_currency, $template_name, $display_times)
    {
        $currency = Currency::getCurrencyInstance($id_currency ?: (int)Configuration::get('PS_CURRENCY_DEFAULT'));
        $language = new Language($id_lang);

        $reminderName = $title;
        if ($id_ets_abancart_reminder == -1)
            $reminderName = $this->l('Manually abandoned cart emails', 'EtsAbancartReminderForm');
        elseif ($id_ets_abancart_reminder == 0)
            $reminderName = $this->l('Leaving website', 'EtsAbancartReminderForm');
        else
            $reminderName = $this->module->displayText('#' . $id_ets_abancart_reminder, 'strong') . ($reminderName !== '' ? ' - ' . $reminderName : '');

        $row = array(
            $this->l('Reminder name: ', 'EtsAbancartReminderForm') . ' ' . $reminderName,
            $this->l('Time sent: ', 'EtsAbancartReminderForm') . $this->module->displayText(date($this->context->language->date_format_full, strtotime($display_times)), 'strong'),
            $this->l('Template: ', 'EtsAbancartReminderForm') . ' ' . ($template_name ?: $this->l('Blank', 'EtsAbancartReminderForm')),
        );

        if ($id_cart_rule > 0 && ($cart_rule = new CartRule((int)$id_cart_rule)) && $cart_rule->id) {
            if ($cart_rule->reduction_percent > 0) {
                $row[] = $this->l('Discount value:', 'EtsAbancartReminderForm') . ' ' . Tools::displayNumber($cart_rule->reduction_percent, $currency) . '%';
            } elseif ((float)$cart_rule->reduction_amount > 0) {
                $reduction_amount = Tools::convertPrice($cart_rule->reduction_amount, $cart_rule->reduction_currency, false);
                $row[] = $this->l('Discount value:', 'EtsAbancartReminderForm') . ' ' . Tools::displayPrice(Tools::convertPrice($reduction_amount, $currency), $currency) . ' ' . ($cart_rule->reduction_tax ? $this->l('(tax incl.)', 'EtsAbancartReminderForm') : $this->l('(tax excl.)', 'EtsAbancartReminderForm'));
            } elseif ($cart_rule->free_shipping)
                $row[] = $this->l('Discount value:', 'EtsAbancartReminderForm') . ' ' . $this->l('Free shipping', 'EtsAbancartReminderForm');

            $row[] = $this->l('Discount code:', 'EtsAbancartReminderForm') . ' ' . $cart_rule->code;

            if ($cart_rule->id > 0) {
                $expiration_date = (strtotime($cart_rule->date_to) - strtotime($cart_rule->date_from)) / 86400;
                $row[] = $this->l('Expiration date:', 'EtsAbancartReminderForm') . ' ' . (int)$expiration_date . ' ' . $this->l('day(s)', 'EtsAbancartReminderForm');
            }
        }

        $row[] = $this->l('Language: ', 'EtsAbancartReminderForm') . ' ' . $this->displayFlag($language) . ' ' . $language->name;

        return $row;
    }

    public function displayFlag($language)
    {
        if ($language instanceof Language) {
            $this->context->smarty->assign([
                'flag' => $language->iso_code && @file_exists($this->module->getLocalPath() . 'views/img/flag/' . Tools::strtolower($language->iso_code) . '.gif') ? $this->module->getPathUri() . 'views/img/flag/' . Tools::strtolower($language->iso_code) . '.gif' : ''
            ]);

            return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/flag.tpl');
        }
        return '';
    }
}