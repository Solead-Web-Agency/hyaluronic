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

class EtsAbancartIndexCustomer
{
    public static function addCustomerIndexScheduleTime()
    {
        $reminder = Db::getInstance()->getRow("
            SELECT ar.* 
            FROM `" . _DB_PREFIX_ . "ets_abancart_reminder` ar 
            LEFT JOIN `" . _DB_PREFIX_ . "ets_abancart_campaign` ac ON ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign 
            WHERE ar.enabled=1 
                AND ar.deleted=0 
                AND ac.enabled=1 
                AND ac.deleted=0 
                AND ac.email_timing_option=" . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME . " 
                AND ar.schedule_time <= '" . pSQL(date('Y-m-d H:i:s')) . "'
            ORDER BY ar.schedule_time DESC
        ");
        if ($reminder) {
            $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                SELECT `id_customer`, `email`, `firstname`, `lastname`
                FROM `' . _DB_PREFIX_ . 'customer`
                WHERE `active` = 1 AND `deleted` = 0 AND `date_add` <= \'' . pSQL($reminder['schedule_time']) . '\'
                ORDER BY `id_customer`
            ');
            $schedule_reminder_ids = [];
            foreach ($customers as $customer) {
                EtsAbancartIndexCustomer::addCustomerIndex(new Customer($customer['id_customer'])
                    , 0
                    , false
                    , false
                    , false
                    , false
                    , true
                    , 0
                    , false
                    , $schedule_reminder_ids
                );
            }
            if ($schedule_reminder_ids)
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_abancart_reminder` SET `enabled`=' . (int)EtsAbancartReminder::REMINDER_STATUS_FINISHED . ' WHERE `id_ets_abancart_reminder` IN (' . implode(',', $schedule_reminder_ids) . ')');
        }
    }

    public static function deleteIndex($id_ets_abancart_campaign = 0, $id_ets_abancart_reminder = 0, $id_customer = 0)
    {
        if ($id_ets_abancart_campaign > 0 && !Validate::isUnsignedInt($id_ets_abancart_campaign)
            || $id_ets_abancart_reminder > 0 && !Validate::isUnsignedInt($id_ets_abancart_reminder)
            || $id_ets_abancart_campaign <= 0 && $id_ets_abancart_reminder <= 0
        ) {
            return false;
        }
        return Db::getInstance()->execute('
            DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer`
            WHERE 1'
            . ($id_ets_abancart_campaign > 0 ? ' AND `id_ets_abancart_campaign`=' . (int)$id_ets_abancart_campaign : '')
            . ($id_ets_abancart_reminder > 0 ? ' AND `id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder : '')
            . ($id_customer > 0 ? ' AND `id_customer`=' . (int)$id_customer : '')
        );
    }

    public static function addCustomerIndex(
          $customer
        , $id_ets_abancart_campaign = 0
        , $isAfterLogin = false
        , $isAfterCustomerCreated = false
        , $isAfterOrder = false
        , $isAfterSubscribeLetter = false
        , $isAfterScheduleTime = false
        , $id_ets_abancart_reminder = 0
        , $reIndex = false // When modify campaign or reminder need reindex upgrade.
        , &$schedule_reminder_ids = []
    )
    {
        if (
            !$customer ||
            !$customer instanceof Customer ||
            $id_ets_abancart_campaign
            && (
                !Validate::isUnsignedInt($id_ets_abancart_campaign) ||
                !($campaign = new EtsAbancartCampaign($id_ets_abancart_campaign)) ||
                $campaign->id < 1 ||
                $campaign->enabled < 1
            ) ||
            $reIndex && !EtsAbancartIndexCustomer::deleteIndex($id_ets_abancart_campaign, $id_ets_abancart_reminder, $customer->id)
        ) {
            return false;
        }
        $current_date = date('Y-m-d');
        $dq = new DbQuery();
        $dq
            ->select('ac.*, ar.*')
            ->from('ets_abancart_campaign', 'ac')
            ->leftJoin('ets_abancart_reminder', 'ar', 'ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign')
            ->leftJoin('ets_abancart_campaign_with_lang', 'cl', 'cl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND cl.id_lang=' . (int)$customer->id_lang)
            ->where('ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\'')
            ->where('ac.enabled = 1 AND ac.deleted = 0')
            ->where('ar.deleted = 0')
            ->where('IF(ac.email_timing_option=' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_RUN_NOW . ', ar.enabled = ' . EtsAbancartReminder::REMINDER_STATUS_FINISHED . ', ar.enabled = ' . EtsAbancartReminder::REMINDER_STATUS_RUNNING . ')')
            ->where('ac.id_shop=' . (int)$customer->id_shop)
            ->where('IF(ac.is_all_lang != 1, cl.id_ets_abancart_campaign is NOT NULL, 1)')
            ->where('IF(ac.available_from is NOT NULL, ac.available_from <= "' . pSQL($current_date) . '", 1) AND IF(ac.available_to is NOT NULL, ac.available_to >= "' . pSQL($current_date) . '", 1)')
            ->groupBy('ac.id_ets_abancart_campaign, ar.id_ets_abancart_reminder');

        if ($isAfterScheduleTime) {
            $dq
                ->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME)
                ->where('ar.schedule_time <= \'' . date('Y-m-d') . '\'')
                ->where('ar.schedule_time >= \'' . pSQL($customer->date_add) . '\'');
        }
        if (!$isAfterCustomerCreated) {// && !$isAfterSubscribeLetter
            $dq
                ->leftJoin('ets_abancart_campaign_country', 'acc', 'acc.id_ets_abancart_campaign = ac.id_ets_abancart_campaign');
            if ($customer->id) {
                $dq
                    ->leftJoin('address', 'a', 'a.id_country = acc.id_country AND a.id_customer=' . (int)$customer->id)
                    ->where('IF(ac.is_all_country != 1, a.id_country > 0 OR acc.id_country = -1, 1)');
            } else
                $dq->where('ac.is_all_country = 1 OR acc.id_country = -1');
        }

        if ($id_ets_abancart_campaign > 0)
            $dq->where('ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign);
        if ($id_ets_abancart_reminder > 0)
            $dq->where('ar.id_ets_abancart_reminder=' . (int)$id_ets_abancart_reminder);

        if ($isAfterLogin) {
            $dq->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN);
        } elseif ($isAfterCustomerCreated) {
            $dq->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION);
        } elseif ($isAfterOrder) {
            $dq->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION);
        } elseif ($isAfterSubscribeLetter) {
            $dq->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER);
        }

        $sendRepeatOptions = array(EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION, EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN);

        if ($res = Db::getInstance()->executeS($dq)) {
            $query = [];
            foreach ($res as $item) {
                if ((int)$item['id_ets_abancart_reminder'] < 1 || !in_array((int)$item['email_timing_option'], $sendRepeatOptions) && (int)Db::getInstance()->getValue('SELECT `id_ets_abancart_reminder` FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` WHERE `id_customer`' . ($customer->id > 0 ? '=' . (int)$customer->id : ' is NULL') . ' AND `email`=\'' . pSQL($customer->email) . '\' AND `id_ets_abancart_reminder`=' . (int)$item['id_ets_abancart_reminder']) > 0) {
                    continue;
                }
                // Subscriber
                if (!$customer->id) {
                    if ((int)$item['email_timing_option'] !== EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER)
                        continue;
                } elseif (in_array((int)$item['email_timing_option'], $sendRepeatOptions)) {
                    if ((int)$item['send_repeat_email'] <= 0) {
                        $customerIsRun = self::getCustomerIsRun($customer->id, (int)$item['id_ets_abancart_reminder']);
                        if ($customerIsRun > 0)
                            continue;
                    } elseif ((int)$item['email_timing_option'] == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN) {
                        if (self::reIndexLastLogin($customer->id, (int)$item['id_ets_abancart_reminder']))
                            continue;
                    }
                }

                if (!$isAfterCustomerCreated && isset($item['has_placed_orders']) && trim($item['has_placed_orders']) !== 'no') {
                    // Order validate
                    if (trim($item['last_order_from']) !== '' ||
                        trim($item['last_order_to']) !== '' ||
                        trim($item['max_total_order']) !== '' ||
                        trim($item['min_total_order']) !== ''
                    ) {
                        $dq = new DbQuery();
                        $dq
                            ->select('o.id_order, o.total_paid_tax_incl, o.date_add')
                            ->from('orders', 'o')
                            ->where('o.id_customer=' . (int)$customer->id)
                            ->orderBy('o.date_add DESC');
                        $last_order = Db::getInstance()->getRow($dq);
                        if (trim($item['last_order_from']) !== '' && (!isset($last_order['date_add']) || !$last_order['date_add'] || strtotime($last_order['date_add']) < strtotime($item['last_order_from'])) ||
                            trim($item['last_order_to']) !== '' && (!isset($last_order['date_add']) || !$last_order['date_add'] || strtotime($last_order['date_add']) > strtotime($item['last_order_to'])) ||
                            trim($item['max_total_order']) !== '' && (!isset($last_order['total_paid_tax_incl']) || !$last_order['total_paid_tax_incl'] || $last_order['total_paid_tax_incl'] > $item['max_total_order']) ||
                            trim($item['min_total_order']) !== '' && (!isset($last_order['total_paid_tax_incl']) || !$last_order['total_paid_tax_incl'] || $last_order['total_paid_tax_incl'] < $item['min_total_order'])
                        ) {
                            continue;
                        }
                    }
                    // Purchased product validate
                    if (trim($item['purchased_product']) !== '' || trim($item['not_purchased_product']) !== '') {
                        $purchased_product = trim($item['purchased_product']) !== '' ? explode(',', $item['purchased_product']) : '';
                        $not_purchased_product = trim($item['not_purchased_product']) !== '' ? explode(',', $item['not_purchased_product']) : '';

                        $dq = new DbQuery();
                        $dq
                            ->select('COUNT(od.product_id)')
                            ->from('orders', 'o')
                            ->leftJoin('order_detail', 'od', 'o.id_order=od.id_order')
                            ->where('o.id_customer=' . (int)$customer->id)
                            ->groupBy('od.product_id');
                        if (is_array($purchased_product) && Validate::isArrayWithIds($purchased_product)) {
                            $dq->where('od.product_id IN (' . implode(',', $purchased_product) . ')');
                        }
                        if (is_array($not_purchased_product) && Validate::isArrayWithIds($not_purchased_product)) {
                            $dq->where('od.product_id NOT IN (' . implode(',', $not_purchased_product) . ')');
                        }
                        if ((int)Db::getInstance()->getValue($dq) < 1) {
                            continue;
                        }
                    }
                }

                // Change status finished:
                if ($isAfterScheduleTime
                    && (int)$item['email_timing_option'] == EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SCHEDULE_TIME
                    && !in_array((int)$item['id_ets_abancart_reminder'], $schedule_reminder_ids)
                ) {
                    $schedule_reminder_ids[] = (int)$item['id_ets_abancart_reminder'];
                }

                $query[] = '(
                    ' . (int)$customer->id . '
                    , ' . (int)$item['id_ets_abancart_reminder'] . '
                    , ' . (int)$item['id_ets_abancart_campaign'] . '
                    , ' . (int)$customer->id_shop . '
                    , \'' . pSQL($customer->firstname) . '\'
                    , \'' . pSQL($customer->lastname) . '\'
                    , \'' . pSQL($customer->email) . '\'
                    , ' . (int)$customer->id_lang . '
                    , ' . ($customer->date_add ? '\'' . pSQL($customer->date_add) . '\'' : 'NULL') . '
                    , ' . ($isAfterLogin ? '\'' . date('Y-m-d H:i:s') . '\'' : 'NULL') . '
                    , ' . ($isAfterSubscribeLetter ? '\'' . date('Y-m-d H:i:s') . '\'' : 'NULL') . '
                    , ' . ($isAfterOrder ? '\'' . date('Y-m-d H:i:s') . '\'' : 'NULL') . '
                    , ' . ($customer->date_upd ? '\'' . pSQL($customer->date_upd) . '\'' : 'NULL') . '
                )';
            }
            if ($query) {
                return Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_index_customer`(
                    `id_customer`
                    , `id_ets_abancart_reminder`
                    , `id_ets_abancart_campaign`
                    , `id_shop`
                    , `firstname`
                    , `lastname`
                    , `email`
                    , `id_lang`
                    , `customer_date_add`
                    , `last_login_time`
                    , `newsletter_date_add`
                    , `last_date_order`
                    , `date_upd`
                ) VALUES' . implode(',', $query));
            }
        }
        return false;
    }

    public static function getCustomerIsRun($id_customer, $id_ets_abancart_reminder, $id_shop = 0)
    {
        if ($id_customer <= 0 || !Validate::isUnsignedInt($id_customer) || $id_ets_abancart_reminder <= 0 || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;

        return (int)Db::getInstance()->getValue('
            SELECT 
                (SELECT COUNT(`id_customer`) FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` WHERE `id_customer` = ' . (int)$id_customer . ' AND `id_ets_abancart_reminder` = ' . (int)$id_ets_abancart_reminder . ($id_shop > 0 ? ' AND `id_shop` = ' . (int)$id_shop : '') . ') +
                (SELECT COUNT(`id_customer`) FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE `id_customer` = ' . (int)$id_customer . ' AND `id_ets_abancart_reminder` = ' . (int)$id_ets_abancart_reminder . ($id_shop > 0 ? ' AND `id_shop` = ' . (int)$id_shop : '') . ') +
                (SELECT COUNT(`id_customer`) FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer` WHERE `id_customer` = ' . (int)$id_customer . ' AND `id_ets_abancart_reminder` = ' . (int)$id_ets_abancart_reminder . ($id_shop > 0 ? ' AND `id_shop` = ' . (int)$id_shop : '') . ')
        ');
    }

    public static function reIndexLastLogin($id_customer, $id_ets_abancart_reminder, $id_shop = 0)
    {
        if ($id_customer <= 0 || !Validate::isUnsignedInt($id_customer) || $id_ets_abancart_reminder <= 0 || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;

        $res = (int)Db::getInstance()->getValue('
            SELECT COUNT(`id_customer`) 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer` 
            WHERE `id_customer` = ' . (int)$id_customer . ' AND `id_ets_abancart_reminder` = ' . (int)$id_ets_abancart_reminder . ($id_shop > 0 ? ' AND `id_shop` = ' . (int)$id_shop : '') . '
        ');
        if ($res)
            return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_abancart_index_customer` SET `last_login_time` = \'' . date('Y-m-d H:i:s') . '\' WHERE `id_customer` = ' . (int)$id_customer . ' AND `id_ets_abancart_reminder` = ' . (int)$id_ets_abancart_reminder . ($id_shop > 0 ? ' AND `id_shop` = ' . (int)$id_shop : ''));
        return false;
    }

    public static function getTotalCustomerIndex($id_ets_abancart_reminder = 0)
    {
        if ($id_ets_abancart_reminder > 0 && !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "ets_abancart_index_customer` WHERE 1 " . ($id_ets_abancart_reminder > 0 ? " AND id_ets_abancart_reminder=" . (int)$id_ets_abancart_reminder : ""));
    }

    /**
     * @param $module Ets_abandonedcart
     * @param $context Context
     */
    public static function getLastVisited($module, $context, $ip_address = null)
    {
        if ($ip_address == null) {
            $ip_address = Tools::getRemoteAddr();
            if ($ip_address == '::1')
                $ip_address = '127.0.0.1';
        }

        $current_date = date('Y-m-d');
        $dq = new DbQuery();
        $dq
            ->select('ac.*, ar.*, (86400*IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0)) `time`')
            ->from('ets_abancart_campaign', 'ac')
            ->leftJoin('ets_abancart_reminder', 'ar', 'ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign')
            ->where('ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\'')
            ->where('ac.enabled = 1 AND ac.deleted = 0')
            ->where('ar.deleted = 0')
            ->where('ac.email_timing_option=' . (int)EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN)
            ->where('ar.enabled = ' . EtsAbancartReminder::REMINDER_STATUS_RUNNING)
            ->where('IF(ac.available_from is NOT NULL, ac.available_from <= "' . pSQL($current_date) . '", 1) AND IF(ac.available_to is NOT NULL, ac.available_to >= "' . pSQL($current_date) . '", 1)')
            ->where('ar.id_ets_abancart_reminder > 0')
            ->groupBy('ac.id_ets_abancart_campaign, ar.id_ets_abancart_reminder');

        if ($res = Db::getInstance()->executeS($dq)) {

            $queueSQL = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_email_queue` VALUES ';
            $trackingSQL = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_tracking`(id_ets_abancart_tracking, id_cart, id_customer, email, ip_address, id_ets_abancart_reminder, id_shop, display_times, total_execute_times, delivered, `read`, `deleted`, date_add, date_upd, customer_last_visit) VALUES ';
            $trackingVoucher = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_discount`(`id_ets_abancart_tracking`, `id_cart_rule`, `use_same_cart`) VALUES ';
            $trackingQueries = [];
            $queueQueries = [];

            foreach ($res as $item) {
                $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
                    SELECT cn.`date_add` AS last_visit, c.*, arl.title, arl.content, arl.discount_name
                    FROM `' . _DB_PREFIX_ . 'connections` cn
                    LEFT JOIN `' . _DB_PREFIX_ . 'guest` g ON(cn.id_guest = g.id_guest)
                    LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON(c.id_customer = g.id_customer)
                    LEFT JOIN `' . _DB_PREFIX_ . 'address` a ON(a.id_customer = c.id_customer)
                    LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_unsubscribers` au ON (c.id_customer = au.id_customer)
                    LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang` cl ON(cl.id_ets_abancart_campaign=' . (int)$item['id_ets_abancart_campaign'] . ' AND cl.id_lang = c.id_lang)
                    LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` acc ON(acc.id_ets_abancart_campaign=' . (int)$item['id_ets_abancart_campaign'] . ' AND acc.id_country = a.id_country)
                    LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ' . (int)$item['id_ets_abancart_campaign'] . ' AND arl.id_lang = c.id_lang)
                    WHERE  c.id_customer > 0
                        AND au.id_customer is NULL
                        AND a.id_address is NOT NULL
                        AND arl.id_ets_abancart_reminder is NOT NULL
                        AND IF(' . (int)$item['is_all_lang'] . ' != 1, cl.id_ets_abancart_campaign is NOT NULL, 1)
                        AND IF(' . (int)$item['is_all_country'] . ' != 1, a.id_country > 0 OR acc.id_country = -1, 1) 
                        AND (' . (int)time() . '- UNIX_TIMESTAMP(cn.date_add)) >= ' . (int)$item['time'] . '
                        AND c.`id_shop` = ' . (int)$item['id_shop'] . '
                    GROUP BY g.`id_customer` 
                    ORDER BY cn.`date_add` DESC 
                ');
                if ($customers) {
                    foreach ($customers as $customer) {

                        if (!isset($customer['id_customer']) ||
                            (int)$customer['id_customer'] < 0 ||
                            !$customer['id_shop'] ||
                            (int)$customer['id_shop'] < 0
                        ) {
                            continue;
                        }
                        if ((int)$item['send_repeat_email'] <= 0) {
                            $customerIsRun = self::getCustomerIsRun($customer['id_customer'], (int)$item['id_ets_abancart_reminder']);
                            if ($customerIsRun > 0)
                                continue;
                        } elseif (
                            (int)Db::getInstance()->getValue('SELECT `id_ets_abancart_reminder` FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` 
                                WHERE `id_customer` = ' . (int)$customer['id_customer'] . ' 
                                    AND `id_ets_abancart_reminder` = ' . (int)$item['id_ets_abancart_reminder']
                            ) > 0 ||
                            (int)Db::getInstance()->getValue('SELECT `id_ets_abancart_reminder` FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
                                WHERE `id_customer` = ' . (int)$customer['id_customer'] . ' 
                                    AND `id_ets_abancart_reminder` = ' . (int)$item['id_ets_abancart_reminder'] . ' AND `customer_last_visit` = "' . pSQL($customer['last_visit']) . '"'
                            ) > 0
                        ) {
                            continue;
                        }
                        if (isset($item['has_placed_orders']) && trim($item['has_placed_orders']) !== 'no') {
                            // Order validate
                            if (trim($item['last_order_from']) !== '' ||
                                trim($item['last_order_to']) !== '' ||
                                trim($item['max_total_order']) !== '' ||
                                trim($item['min_total_order']) !== ''
                            ) {
                                $dq = new DbQuery();
                                $dq
                                    ->select('o . id_order, o . total_paid_tax_incl, o . date_add')
                                    ->from('orders', 'o')
                                    ->where('o . id_customer = ' . (int)$customer['id_customer'])
                                    ->orderBy('o . date_add DESC');
                                $last_order = Db::getInstance()->getRow($dq);
                                if (trim($item['last_order_from']) !== '' && (!isset($last_order['date_add']) || !$last_order['date_add'] || strtotime($last_order['date_add']) < strtotime($item['last_order_from'])) ||
                                    trim($item['last_order_to']) !== '' && (!isset($last_order['date_add']) || !$last_order['date_add'] || strtotime($last_order['date_add']) > strtotime($item['last_order_to'])) ||
                                    trim($item['max_total_order']) !== '' && (!isset($last_order['total_paid_tax_incl']) || !$last_order['total_paid_tax_incl'] || $last_order['total_paid_tax_incl'] > $item['max_total_order']) ||
                                    trim($item['min_total_order']) !== '' && (!isset($last_order['total_paid_tax_incl']) || !$last_order['total_paid_tax_incl'] || $last_order['total_paid_tax_incl'] < $item['min_total_order'])
                                ) {
                                    continue;
                                }
                            }
                            // Purchased product validate
                            if (trim($item['purchased_product']) !== '' || trim($item['not_purchased_product']) !== '') {
                                $purchased_product = trim($item['purchased_product']) !== '' ? explode(',', $item['purchased_product']) : '';
                                $not_purchased_product = trim($item['not_purchased_product']) !== '' ? explode(',', $item['not_purchased_product']) : '';

                                $dq = new DbQuery();
                                $dq
                                    ->select('COUNT(od . product_id)')
                                    ->from('orders', 'o')
                                    ->leftJoin('order_detail', 'od', 'o . id_order = od . id_order')
                                    ->where('o . id_customer = ' . (int)$customer['id_customer'])
                                    ->groupBy('od . product_id');
                                if (is_array($purchased_product) && Validate::isArrayWithIds($purchased_product)) {
                                    $dq->where('od . product_id IN(' . implode(', ', $purchased_product) . ')');
                                }
                                if (is_array($not_purchased_product) && Validate::isArrayWithIds($not_purchased_product)) {
                                    $dq->where('od . product_id NOT IN(' . implode(', ', $not_purchased_product) . ')');
                                }
                                if ((int)Db::getInstance()->getValue($dq) < 1) {
                                    continue;
                                }
                            }
                        }

                        // New context:
                        $context->shop = new Shop((int)$customer['id_shop']);
                        $context->customer = new Customer((int)$customer['id_customer']);
                        $context->language = new Language(isset($customer['id_lang']) ? (int)$customer['id_lang'] : (int)Configuration::get('PS_LANG_DEFAULT', null, null, $context->shop->id));
                        if (isset($context->cart->id_currency) && $context->cart->id_currency !== $context->currency->id && ($currency = new Currency($context->cart->id_currency)) && $currency->id > 0) {
                            $context->currency = $currency;
                            EtsAbancartTools::getLocale($context);
                        }
                        $tpl_vars = [
                            'customer' => $context->customer,
                            'campaign_type' => 'customer',
                            'content' => $customer['content']
                        ];
                        if ($item['discount_option'] == 'auto') {
                            $reminder = new EtsAbancartReminder((int)$item['id_ets_abancart_reminder']);
                            $tpl_vars['cart_rule'] = $module->addCartRule($reminder, (int)$customer['id_customer']);
                        } elseif ($item['discount_option'] != 'no') {
                            $tpl_vars['cart_rule'] = new CartRule(!empty($item['discount_code']) ? (int)CartRule::getIdByCode($item['discount_code']) : null);
                        } else
                            $tpl_vars['cart_rule'] = new CartRule();

                        if (!isset($context->cart)) {
                            $context->cart = new Cart();
                        }
                        $content = $module->doShortCode($tpl_vars['content'], 'customer', $tpl_vars['cart_rule'], $context, (int)$item['id_ets_abancart_reminder']);
                        $content = EtsAbancartEmailTemplate::createContentEmailToSend($content, (int)$item['id_ets_abancart_reminder'], $context->language->id);

                        $queueQueries[] = '(NULL, ' . (int)$context->shop->id . ', ' . (int)$context->language->id . ', NULL, ' . ((int)$customer['id_customer'] > 0 ? (int)$customer['id_customer'] : 'NULL') . ', ' . (int)$item['id_ets_abancart_reminder'] . ', "' . pSQL($customer['firstname'] . ' ' . $customer['lastname']) . '", "' . pSQL($customer['email']) . '", "' . pSQL($customer['title']) . '", "' . pSQL($content, true) . '", 0, NULL, 0, "' . pSQL(date('Y-m-d H:i:s')) . '")';
                        $trackingQueries[] = $trackingSQL . '(NULL, NULL, ' . ((int)$customer['id_customer'] > 0 ? (int)$customer['id_customer'] : 'NULL') . ',"' . $customer['email'] . '", "' . $ip_address . '",' . (int)$item['id_ets_abancart_reminder'] . ', ' . (int)$customer['id_shop'] . ', "' . pSQL(date('Y-m-d H:i:s')) . '", 0, 0, 0, 0, "' . pSQL(date('Y-m-d H:i:s')) . '", "' . pSQL(date('Y-m-d H:i:s')) . '", "' . pSQL($customer['last_visit']) . '")';
                        if ((int)$tpl_vars['cart_rule']->id > 0) {
                            $trackingQueries[] = $trackingVoucher . '(LAST_INSERT_ID(), ' . (int)$tpl_vars['cart_rule']->id . ', ' . (int)$item['allow_multi_discount'] . ')';
                        }
                    }
                }
            }
            if ($queueQueries)
                Db::getInstance()->execute($queueSQL . implode(',', $queueQueries) . ';');
            if ($trackingQueries)
                Db::getInstance()->execute(implode(';', $trackingQueries));
        }
    }
}