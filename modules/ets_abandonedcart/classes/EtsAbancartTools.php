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

class EtsAbancartTools extends EtsAbancartCore
{
    public static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new EtsAbancartTools();
        }
        return self::$instance;
    }

    public static function getContentQueue($id)
    {
        if (!$id || !Validate::isUnsignedInt($id)) {
            return false;
        }
        $dq = new DbQuery();
        $dq
            ->select('a.content')
            ->from('ets_abancart_email_queue', 'a')
            ->where('id_ets_abancart_email_queue=' . (int)$id);
        return Db::getInstance()->getValue($dq);
    }

    public static function getQueue($id)
    {
        if (!$id || !Validate::isUnsignedInt($id)) {
            return false;
        }
        $dq = new DbQuery();
        $dq
            ->select('*')
            ->from('ets_abancart_email_queue')
            ->where('id_ets_abancart_email_queue=' . (int)$id);
        return Db::getInstance()->getRow($dq);
    }

    public static function getLocale(Context $context)
    {
        if ($context && version_compare(_PS_VERSION_, '1.7.6.0', '>')) {
            $container = call_user_func_array('PrestaShop\PrestaShop\Adapter\ContainerBuilder::getContainer', array('front', _PS_MODE_DEV_));
            $localeRepo = $container->get('prestashop.core.localization.locale.repository');
            $context->currentLocale = $localeRepo->getLocale(
                $context->language->getLocale()
            );
        }
    }

    public static function getColor($num)
    {
        $hash = md5('color' . $num);
        $rgb = array(
            hexdec(Tools::substr($hash, 0, 2)), // r
            hexdec(Tools::substr($hash, 2, 2)), // g
            hexdec(Tools::substr($hash, 4, 2))); //b
        return 'rgba(' . implode(',', $rgb) . ', %s)';
    }

    public static function isArrayWithIds($ids)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                if ($id == 0 || !Validate::isInt($id)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getImageType($name)
    {
        return version_compare(_PS_VERSION_, '1.7', '>=') ? ImageType::getFormattedName($name) : ImageType::getFormatedName($name);
    }

    public function runCronjob($id_shop = null, $manual = null)
    {
        Configuration::updateGlobalValue('ETS_ABANCART_LAST_CRONJOB', date('Y-m-d H:i:s'), true);

        /**
         * @param $module Ets_abandonedcart
         */
        $module = Module::getInstanceByName('ets_abandonedcart');

        $insertAcSql = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_%s` VALUES';
        if (!($token = trim(Tools::getValue('secure'))) || !Validate::isCleanHtml($token) || $token != Configuration::getGlobalValue('ETS_ABANCART_SECURE_TOKEN')) {
            if (Tools::isSubmit('ajax')) {
                die(Tools::jsonEncode(array(
                    'errors' => $this->l('Access denied'),
                    'result' => ''
                )));
            }
            die($this->l('Access denied'));
        }

        // Clear discount:
        $totalDiscountDeleted = 0;
        if ((int)Configuration::get('ETS_ABANCART_AUTO_CLEAR_DISCOUNT')) {
            $totalDiscountDeleted = EtsAbancartIndex::clearDiscountIsExpired();
        }
        // End:

        $context = Context::getContext();
        // Backup:
        $keeps = [
            'currency' => $context->currency,
            'shop' => $context->shop,
            'cart' => $context->cart,
            'customer' => $context->customer,
        ];

        $context->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        EtsAbancartTools::getLocale($context);
        $ip_address = Tools::getRemoteAddr();
        if ($ip_address == '::1') $ip_address = '127.0.0.1';

        /*-------------------------------------EMAIL-INDEX-------------------------------------*/
        $sql = '
            SELECT (86400 * IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0)) `lifetime`
                , TIMESTAMPDIFF(SECOND , ai.cart_date_add, "' . pSQL(date('Y-m-d H:i:s')) . '") `overtime`
                , rl.*
                , ai.*
                , ar.*
            FROM `' . _DB_PREFIX_ . 'ets_abancart_index` ai
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = ai.id_ets_abancart_reminder)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` rl ON (rl.id_ets_abancart_reminder = ai.id_ets_abancart_reminder AND rl.id_lang = ai.id_cart_lang)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_unsubscribers` au ON (ai.id_customer = au.id_customer)
            WHERE au.id_customer is NULL AND ar.id_ets_abancart_reminder is NOT NULL' . ($id_shop ? ' AND ai.id_shop=' . (int)$id_shop : '') . '
            HAVING `overtime` >= `lifetime`
        ';

        if ($jobs = Db::getInstance()->executeS($sql)) {

            $queueSQL = sprintf($insertAcSql, 'email_queue');
            $trackingSQL = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_tracking`(id_ets_abancart_tracking, id_cart, id_customer, email, ip_address, id_ets_abancart_reminder, id_shop, display_times, total_execute_times, delivered, `read`, `deleted`, date_add, date_upd) VALUES ';
            $trackingVoucher = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_discount`(`id_ets_abancart_tracking`, `id_cart_rule`, `use_same_cart`) VALUES ';
            $trackingQueries = [];

            foreach ($jobs as $job) {
                // Validate:
                if (!isset($job['id_cart']) ||
                    (int)$job['id_cart'] < 0 ||
                    !isset($job['id_customer']) ||
                    (int)$job['id_customer'] < 0 ||
                    !$job['id_shop'] ||
                    (int)$job['id_shop'] < 0
                ) {
                    continue;
                }

                // setContext:
                $context->cart = new Cart((int)$job['id_cart']);
                $has_applied_voucher = ($cart_rules = $context->cart->getCartRules()) && is_array($cart_rules) && count($cart_rules) > 0 ? 1 : 0;
                if (isset($job['has_applied_voucher']) && (trim($job['has_applied_voucher']) === 'yes' && $has_applied_voucher === 0 || trim($job['has_applied_voucher']) === 'no' && $has_applied_voucher > 0)) {
                    continue;
                }
                $context->shop = new Shop((int)$job['id_shop']);
                $context->customer = new Customer((int)$job['id_customer']);
                $context->language = new Language(isset($job['id_cart_lang']) ? (int)$job['id_cart_lang'] : (int)Configuration::get('PS_LANG_DEFAULT', null, null, $context->shop->id));
                if ($context->cart->id_currency !== $context->currency->id && ($currency = new Currency($context->cart->id_currency)) && $currency->id > 0) {
                    $context->currency = $currency;
                    EtsAbancartTools::getLocale($context);
                }

                // Discount:
                if ($job['discount_option'] == 'auto') {
                    $reminder = new EtsAbancartReminder((int)$job['id_ets_abancart_reminder']);
                    $cart_rule = $module->addCartRule($reminder, $context->customer->id);
                } elseif ($job['discount_option'] != 'no') {
                    $id_cart_rule = isset($job['discount_code']) && $job['discount_code'] ? (int)CartRule::getIdByCode($job['discount_code']) : 0;
                    $cart_rule = new CartRule($id_cart_rule ?: null);
                } else {
                    $cart_rule = new CartRule();
                }

                $content = $module->doShortCode($job['content'], 'email', $cart_rule, $context, (int)$job['id_ets_abancart_reminder']);
                $content = EtsAbancartEmailTemplate::createContentEmailToSend($content, (int)$job['id_ets_abancart_reminder'], $context->language->id);

                // To SQL
                $queueSQL .= '(NULL, ' . (int)$context->shop->id . ',' . (int)$context->language->id . ', ' . (int)$job['id_cart'] . ', ' . (int)$job['id_customer'] . ', ' . (int)$job['id_ets_abancart_reminder'] . ', "' . pSQL($job['firstname'] . ' ' . $job['lastname']) . '", "' . pSQL($job['email']) . '", "' . pSQL($job['title']) . '", "' . pSQL($content, true) . '", 0, NULL, 0, "' . pSQL(date('Y-m-d H:i:s')) . '"),';
                $trackingQueries[] = $trackingSQL . '(NULL, ' . (int)$job['id_cart'] . ', NULL, "' . pSQL($job['email']) . '", "' . $ip_address . '",' . (int)$job['id_ets_abancart_reminder'] . ', ' . (int)$job['id_shop'] . ', "' . pSQL(date('Y-m-d H:i:s')) . '",0, 0, 0, 0, "' . pSQL(date('Y-m-d H:i:s')) . '", "' . pSQL(date('Y-m-d H:i:s')) . '")';
                if ($cart_rule->id > 0) {
                    $trackingQueries[] = $trackingVoucher . '(LAST_INSERT_ID(), ' . (int)$cart_rule->id . ', ' . (int)$job['allow_multi_discount'] . ')';
                }
                // Remove index:
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_index` WHERE id_cart=' . (int)$job['id_cart'] . ' AND id_ets_abancart_reminder=' . (int)$job['id_ets_abancart_reminder'] . ' AND id_ets_abancart_campaign=' . (int)$job['id_ets_abancart_campaign']);
            }
            Db::getInstance()->execute(rtrim($queueSQL, ','));
            Db::getInstance()->execute(implode(';', $trackingQueries));
        }

        /*-------------------------------------END EMAIL-INDEX--------------------------------*/

        /*-------------------------------------CUSTOMER-INDEX--------------------------------*/
        // Schedule time to index:
        EtsAbancartIndexCustomer::addCustomerIndexScheduleTime();

        $sql = '
            SELECT ac.id_ets_abancart_campaign,ac.email_timing_option,
                   (86400 * IFNULL(ar.day, 0) + 3600*IFNULL(ar.hour, 0)) `lifetime`
                   , TIMESTAMPDIFF(SECOND, IF(ac.email_timing_option=' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN . ', ic.last_login_time, IF(ac.email_timing_option=' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER . ', ic.newsletter_date_add, IF(ac.email_timing_option=' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION . ', ic.last_date_order, ic.customer_date_add))), \'' . pSQL(date('Y-m-d H:i:s')) . '\') `overtime`
                   , ar.*
                   , arl.title
                   , arl.content
                   , arl.discount_name
                   , ic.*
            FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer` ic
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ic.id_ets_abancart_campaign  = ac.id_ets_abancart_campaign )
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_unsubscribers` au ON (ic.id_customer = au.id_customer)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND ar.id_ets_abancart_reminder = ic.id_ets_abancart_reminder)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang = ic.id_lang)
            WHERE au.id_customer is NULL
                AND ar.id_ets_abancart_reminder is NOT NULL 
                ' . ($id_shop ? ' AND ic.id_shop=' . (int)$id_shop : '') . '
            GROUP BY ac.id_ets_abancart_campaign, ar.id_ets_abancart_reminder, ic.id_customer
            HAVING IF(ac.email_timing_option IN(' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_REGISTRATION . ',' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_ORDER_COMPLETION . ',' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_LAST_TIME_LOGIN . ',' . EtsAbancartReminder::CUSTOMER_EMAIL_SEND_AFTER_SUBSCRIBE_LETTER . '), overtime >= lifetime, 1)
        ';

        if ($cJobs = Db::getInstance()->executeS($sql)) {
            // Struct:
            $queueSQL = sprintf($insertAcSql, 'email_queue');
            $trackingSQL = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_tracking`(id_ets_abancart_tracking, id_cart, id_customer, email, ip_address, id_ets_abancart_reminder, id_shop, display_times, total_execute_times, delivered, `read`, `deleted`, date_add, date_upd) VALUES ';
            $trackingVoucher = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_discount`(`id_ets_abancart_tracking`, `id_cart_rule`, `use_same_cart`) VALUES ';
            $trackingQueries = [];

            foreach ($cJobs as $job) {
                if (!isset($job['id_customer']) ||
                    (int)$job['id_customer'] < 0 ||
                    !$job['id_shop'] ||
                    (int)$job['id_shop'] < 0
                ) {
                    continue;
                }
                // New context:
                $context->shop = new Shop((int)$job['id_shop']);
                $context->customer = new Customer((int)$job['id_customer']);
                $context->language = new Language(isset($job['id_lang']) ? (int)$job['id_lang'] : (int)Configuration::get('PS_LANG_DEFAULT', null, null, $context->shop->id));
                if (isset($context->cart->id_currency) && $context->cart->id_currency !== $context->currency->id && ($currency = new Currency($context->cart->id_currency)) && $currency->id > 0) {
                    $context->currency = $currency;
                    EtsAbancartTools::getLocale($context);
                }
                $tpl_vars = [
                    'customer' => $context->customer,
                    'campaign_type' => 'customer',
                    'content' => $job['content']
                ];
                if ($job['discount_option'] == 'auto') {
                    $reminder = new EtsAbancartReminder((int)$job['id_ets_abancart_reminder']);
                    $tpl_vars['cart_rule'] = $module->addCartRule($reminder, (int)$job['id_customer']);
                } elseif ($job['discount_option'] != 'no') {
                    $tpl_vars['cart_rule'] = new CartRule(!empty($job['discount_code']) ? (int)CartRule::getIdByCode($job['discount_code']) : null);
                } else
                    $tpl_vars['cart_rule'] = new CartRule();

                if (!isset($context->cart)) {
                    $context->cart = new Cart();
                }
                $content = $module->doShortCode($tpl_vars['content'], 'customer', $tpl_vars['cart_rule'], $context, (int)$job['id_ets_abancart_reminder']);
                $content = EtsAbancartEmailTemplate::createContentEmailToSend($content, (int)$job['id_ets_abancart_reminder'], $context->language->id);

                $queueSQL .= '(NULL, ' . (int)$context->shop->id . ',' . (int)$context->language->id . ', NULL, ' . ((int)$job['id_customer'] > 0 ? (int)$job['id_customer'] : 'NULL') . ', ' . (int)$job['id_ets_abancart_reminder'] . ', "' . pSQL($job['firstname'] . ' ' . $job['lastname']) . '", "' . pSQL($job['email']) . '", "' . pSQL($job['title']) . '", "' . pSQL($content, true) . '", 0, NULL, 0, "' . pSQL(date('Y-m-d H:i:s')) . '"),';
                $trackingQueries[] = $trackingSQL . '(NULL, NULL, ' . ((int)$job['id_customer'] > 0 ? (int)$job['id_customer'] : 'NULL') . ',"' . $job['email'] . '", "' . $ip_address . '",' . (int)$job['id_ets_abancart_reminder'] . ', ' . (int)$job['id_shop'] . ', "' . pSQL(date('Y-m-d H:i:s')) . '", 0, 0, 0, 0, "' . pSQL(date('Y-m-d H:i:s')) . '", "' . pSQL(date('Y-m-d H:i:s')) . '")';
                if ((int)$tpl_vars['cart_rule']->id > 0) {
                    $trackingQueries[] = $trackingVoucher . '(LAST_INSERT_ID(), ' . (int)$tpl_vars['cart_rule']->id . ', ' . (int)$job['allow_multi_discount'] . ')';
                }

                // Remove index:
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_index_customer` WHERE id_customer=' . (int)$job['id_customer'] . ' AND id_ets_abancart_reminder=' . (int)$job['id_ets_abancart_reminder'] . ' AND id_ets_abancart_campaign=' . (int)$job['id_ets_abancart_campaign']);
            }

            Db::getInstance()->execute(rtrim($queueSQL, ','));
            Db::getInstance()->execute(implode(';', $trackingQueries));
        }


        /*-------------------------------------END CUSTOMER-INDEX--------------------------------*/


        /*------------------------------------PUSH TO QUEUE LAST VISITED--------------------------*/
        EtsAbancartIndexCustomer::getLastVisited($module, $context, $ip_address);
        /*------------------------------------END PUSH TO QUEUE LAST VISITED--------------------------*/


        /*-------------------------------------SEND MAIL-----------------------------------*/
        $count = 0;
        $max_try = ($max = (int)Configuration::getGlobalValue('ETS_ABANCART_CRONJOB_MAX_TRY')) && $max > 0 && Validate::isUnsignedInt($max) ? $max : 5;
        $max_emails = ($limit = (int)Configuration::getGlobalValue('ETS_ABANCART_CRONJOB_EMAILS')) && $limit > 0 && Validate::isUnsignedInt($limit) ? $limit : 5;

        if ($queues = Db::getInstance()->executeS('
            SELECT * FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` 
            WHERE (sent = 0 AND send_count < ' . (int)$max_try . ') OR sending_time is NULL OR TIMESTAMPDIFF(SECOND, sending_time, \'' . pSQL(date('Y-m-d H:i:s')) . '\') > 60
            LIMIT ' . (int)$max_emails
        )) {
            foreach ($queues as $queue) {
                if (isset($queue['id_cart']) && $queue['id_cart'] > 0 && EtsAbancartTools::cleanQueueOrdered((int)$queue['id_cart'])) {
                    continue;
                }
                if (Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_abancart_email_queue` SET `sent` = 1, `sending_time` = \'' . pSQL(date('Y-m-d H:i:s')) . '\' WHERE `id_ets_abancart_email_queue` = ' . (int)$queue['id_ets_abancart_email_queue'])) {
                    $URLs = array(
                        'r' => $queue['id_ets_abancart_reminder'],
                        'email' => trim($queue['email'])
                    );
                    if ((int)$queue['id_cart'] > 0)
                        $URLs['c'] = (int)$queue['id_cart'];
                    elseif ((int)$queue['id_customer'] > 0)
                        $URLs['cus'] = (int)$queue['id_customer'];

                    if (!@glob($module->getLocalPath() . 'mails/' . Language::getIsoById((int)$queue['id_lang']) . '/abandoned_cart*[.txt|.html]'))
                        $module->_installMail(new Language((int)$queue['id_lang']));

                    $trackingURL = Context::getContext()->link->getModuleLink($module->name, 'image', $URLs, (int)Configuration::getGlobalValue('PS_SSL_ENABLED_EVERYWHERE'), (int)$queue['id_lang'], (int)$queue['id_shop']) . '&' . md5(time());

                    EtsAbancartTools::saveMailLog($queue, EtsAbancartMail::SEND_MAIL_TIMEOUT);
                    if (EtsAbancartMail::send(
                        (int)$queue['id_lang'],
                        'abandoned_cart',
                        $queue['subject'],
                        array(
                            '{tracking}' => $trackingURL,
                            '{context}' => $queue['content']
                        ),
                        $queue['email'],
                        $queue['customer_name'], null, null, null, null,
                        $module->getLocalPath() . 'mails/', false,
                        (int)$queue['id_shop']
                    )) {
                        EtsAbancartTools::saveMailLog($queue, EtsAbancartMail::SEND_MAIL_DELIVERED);
                        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE `id_ets_abancart_email_queue` = ' . (int)$queue['id_ets_abancart_email_queue']);
                        Db::getInstance()->execute('
                            UPDATE `' . _DB_PREFIX_ . 'ets_abancart_tracking`
                            SET 
                                `delivered` = 1,
                                `total_execute_times` = `total_execute_times` + 1,
                                `display_times` = \'' . pSQL(date('Y-m-d H:i:s')) . '\'
                            WHERE id_cart' . ((int)$queue['id_cart'] > 0 ? '=' . (int)$queue['id_cart'] : ' is NULL') . ' AND id_customer' . ((int)$queue['id_cart'] < 1 ? '=' . (int)$queue['id_customer'] : ' is NULL') . ' AND email=\'' . pSQL(trim($queue['email'])) . '\' AND id_ets_abancart_reminder = ' . (int)$queue['id_ets_abancart_reminder']
                        );
                        $count++;
                    } else {
                        EtsAbancartTools::saveMailLog($queue, EtsAbancartMail::SEND_MAIL_FAILED);
                        Db::getInstance()->execute('
                            UPDATE `' . _DB_PREFIX_ . 'ets_abancart_email_queue` SET `sent` = 0, `send_count` = `send_count` + 1, `sending_time` = \'' . pSQL(date('Y-m-d H:i:s')) . '\' 
                            WHERE `id_ets_abancart_email_queue` = ' . (int)$queue['id_ets_abancart_email_queue']
                        );
                        Db::getInstance()->execute('
                            UPDATE `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
                            SET 
                                `total_execute_times` = `total_execute_times` + 1,
                                `display_times` = \'' . pSQL(date('Y-m-d H:i:s')) . '\'
                            WHERE id_cart' . ((int)$queue['id_cart'] > 0 ? '=' . (int)$queue['id_cart'] : ' is NULL') . ' AND id_customer' . ((int)$queue['id_cart'] < 1 ? '=' . (int)$queue['id_customer'] : ' is NULL') . ' AND email=\'' . pSQL($queue['email']) . '\' AND id_ets_abancart_reminder = ' . (int)$queue['id_ets_abancart_reminder']
                        );
                    }
                }
            }
        }

        Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE send_count >= ' . (int)$max_try);

        /*------------------------------------END SEND MAIL-------------------------------*/

        // restore:
        if ((int)Configuration::getGlobalValue('ETS_ABANCART_SAVE_CRONJOB_LOG')) {
            $return = date($context->language->date_format_full);
            $msg = $this->l('There were %d emails sent successfully');
            if ($count > 0 && $count <= 1)
                $return .= '  ' . sprintf($msg, $count, '');
            elseif ($count > 1)
                $return .= '  ' . sprintf($msg, $count, 's');
            else
                $return .= '  ' . $this->l('No email has been sent');
            if ($totalDiscountDeleted) {
                $return .= " | " . sprintf($this->l('%s discount deleted'), $totalDiscountDeleted);
            } else {
                $return .= " | " . $this->l('No discount deleted');
            }
            $dest = _PS_CACHE_DIR_ . '/' . $module->name;
            if (!@is_dir($dest))
                @mkdir($dest, 0755, true);

            @file_put_contents($dest . '/cronjob.log', $return . "\r\n", FILE_APPEND);
        }

        // Restore:
        foreach ($keeps as $key => $keep) {
            $context->$key = $keep;
        }
        // Return jsonData.
        $jsonArr = array(
            'result' => $this->l('Cronjob ran successfully') . ' ' . ($count <= 0 ? $totalDiscountDeleted ? '. ' . sprintf($this->l('%s discount deleted'), $totalDiscountDeleted) : '. ' . $this->l('Nothing to do!') : sprintf($this->l('%s email(s) was sent!'), $count)),
        );
        if ($id_shop && isset($return)) {
            $jsonArr['log'] = $return;
        }
        if ($manual) {
            $jsonArr['status'] = $module->hookDisplayCronjobInfo();
        }
        die(Tools::jsonEncode($jsonArr));
    }

    public static function saveMailLog($queue, $status)
    {
        if ((int)Configuration::getGlobalValue('ETS_ABANCART_CRONJOB_MAIL_LOG') < 1)
            return true;
        return Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_mail_log`(
                `id_ets_abancart_email_queue`
                , `id_shop`
                , `id_lang`
                , `id_cart`
                , `id_customer`
                , `id_ets_abancart_reminder`
                , `customer_name`
                , `email`
                , `subject`
                , `content`
                , `sent_time`
                , `status`
            ) VALUES (
                ' . (int)$queue['id_ets_abancart_email_queue'] . '
                , ' . (int)$queue['id_shop'] . '
                , ' . (int)$queue['id_lang'] . '
                , ' . (int)$queue['id_cart'] . '
                , ' . (int)$queue['id_customer'] . '
                , ' . (int)$queue['id_ets_abancart_reminder'] . '
                , \'' . pSQL($queue['customer_name']) . '\'
                , \'' . pSQL($queue['email']) . '\'
                , \'' . pSQL($queue['subject']) . '\'
                , \'' . pSQL($queue['content'], true) . '\'
                , \'' . pSQL(date('Y-m-d H:i:s')) . '\'
                , ' . (int)$status . '
            ) ON DUPLICATE KEY UPDATE `sent_time`=\'' . pSQL(date('Y-m-d H:i:s')) . '\', `status` = ' . (int)$status . '
        ');
    }

    public static function cleanQueueOrdered($id_cart)
    {
        $res = false;
        if ($id_cart > 0 && (int)Db::getInstance()->getValue('SELECT id_order FROM`' . _DB_PREFIX_ . 'orders`WHERE id_cart=' . (int)$id_cart) > 0) {
            $res = Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE `id_cart` = ' . (int)$id_cart);
        }
        return $res;
    }

    public static function getLastOrderCustomer($id_customer)
    {
        return Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "orders` WHERE id_customer=" . (int)$id_customer . " ORDER BY id_order DESC");
    }

    public static function getTotalOrder($id_customer)
    {
        return (float)Db::getInstance()->getValue("SELECT SUM(total_paid_tax_incl*conversion_rate) as total_order FROM `" . _DB_PREFIX_ . "orders` WHERE id_customer=" . (int)$id_customer);
    }

    public static function getLastLoginTime($id_customer)
    {
        return Db::getInstance()->getValue("
            SELECT cn.date_add FROM `" . _DB_PREFIX_ . "connections` cn 
            LEFT JOIN `" . _DB_PREFIX_ . "guest` g ON g.id_guest=cn.id_guest
            WHERE g.id_customer=" . (int)$id_customer . " ORDER BY cn.date_add DESC");
    }

    public static function getTotalCustomerInTime($datetime, $groups)
    {
        return (int)Db::getInstance()->getValue("
                    SELECT COUNT(*) as total_customer FROM `" . _DB_PREFIX_ . "customer` c 
                    LEFT JOIN`" . _DB_PREFIX_ . "group`c ON c.id_customer=g.id_customer
                    WHERE data_add<='" . date('Y-m-d H:i:s', strtotime($datetime)) . "' AND g.id_group IN (" . implode(',', $groups) . ") 
                    GROUP BY c.id_customer");
    }

    public static function createMailUploadFolder()
    {
        $mailDir = _ETS_AC_MAIL_UPLOAD_DIR_;
        if (!is_dir(_ETS_AC_IMG_DIR_)) {
            @mkdir(_ETS_AC_IMG_DIR_, 0755);
            @copy(dirname(__FILE__) . '/index.php', _ETS_AC_IMG_DIR_ . '/index.php');
        }
        if (!is_dir($mailDir)) {
            @mkdir($mailDir, 0755);
            @copy(dirname(__FILE__) . '/index.php', $mailDir . '/index.php');
        }
        return is_dir($mailDir) ? $mailDir : null;
    }

    public static function copyFolder($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyFolder($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public static function deleteAllDataInFolder($dir)
    {
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file))
                self::deleteAllDataInFolder($file);
            else
                unlink($file);
        }
        rmdir($dir);
    }

    public static function request($type, $uri, $params = array(), $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        if ($params && Tools::strtoupper($type) === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', http_build_query($params)));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function canUseCartRule($id_cart, $id_cart_rule, &$voucherCode)
    {
        $hasOtherCartRule = false;
        $sendMailVoucher = EtsAbancartTracking::isVoucher($id_cart_rule);
        $popupVoucher = EtsAbancartDisplayTracking::isVoucher($id_cart_rule);
        if (!EtsAbancartTracking::cartRuleValidity($id_cart, $id_cart_rule) && $sendMailVoucher || !EtsAbancartDisplayTracking::cartRuleValidity($id_cart, $id_cart_rule) && $popupVoucher) {
            $hasOtherCartRule = true;
        } elseif ($sendMailVoucher || $popupVoucher) {
            $id_other_cart_rule = (int)Db::getInstance()->getValue("SELECT ccr.id_cart_rule FROM `" . _DB_PREFIX_ . "cart_cart_rule` ccr WHERE id_cart=" . (int)$id_cart . " AND id_cart_rule !=" . (int)$id_cart_rule);
            if ($id_other_cart_rule) {
                $hasOtherCartRule = true;
                $voucher_is_same_cart = $sendMailVoucher ? EtsAbancartTracking::getVoucherIsSameCart($id_cart_rule) : EtsAbancartDisplayTracking::getVoucherIsSameCart($id_cart_rule);
                if ($voucher_is_same_cart) {
                    $hasOtherCartRule = false;
                }
            }
        } elseif (($id_other_cart_rule = EtsAbancartTracking::getVoucherNotIsSameCart($id_cart)) || ($id_other_cart_rule = EtsAbancartDisplayTracking::getVoucherNotIsSameCart($id_cart))) {
            $hasOtherCartRule = true;
        }
        if ($hasOtherCartRule) {
            $cartRule = new CartRule($id_other_cart_rule);
            $voucherCode = $cartRule->code;
            return false;
        }
        return true;
    }

    public function checkCartRuleValidity()
    {
        if (Tools::getValue('controller') == 'cart' &&
            Tools::isSubmit('addDiscount') &&
            Tools::isSubmit('ajax') &&
            !Tools::getIsset('fc') &&
            !Tools::getIsset('module')
        ) {
            $error = '';
            $code = trim(Tools::getValue('discount_name'));
            if ($code == '' || !Validate::isCleanHtml($code)) {
                $error = $this->l('Your voucher code does not exist');
            } else {
                // If voucher by module promotion then true:
                if ($code && Module::isEnabled('ets_promotion')) {
                    if (Db::getInstance()->getRow('SELECT * FROM`' . _DB_PREFIX_ . 'ets_pr_rule`WHERE active=1 AND code="' . pSQL($code) . '"'))
                        return true;
                }

                if ($id_cart_rule = CartRule::getIdByCode($code)) {
                    $voucherCode = null;
                    if (!EtsAbancartTools::canUseCartRule($this->context->cart->id, $id_cart_rule, $voucherCode)) {
                        $error = sprintf($this->l('Cannot use voucher code %s with others voucher code'), $voucherCode);
                    }
                }
            }
            if ($error) {
                die(json_encode(array(
                    'errors' => array($error),
                    'hasError' => true,
                    'quantity' => null,
                )));
            }
        }
    }

    public static function getRandomIdProduct($limit = 1, $context = null)
    {
        if ($context == null)
            $context = Context::getContext();
        $res = Db::getInstance()->executeS("SELECT `id_product` FROM `" . _DB_PREFIX_ . "product_shop` WHERE id_shop=" . (int)$context->shop->id . " ORDER BY rand() LIMIT " . (int)$limit);
        $ids = array();
        if ($res) {
            foreach ($res as $item) {
                $ids[] = (int)$item['id_product'];
            }
        }
        return $ids;
    }

    public static function createImgDir()
    {
        if (!is_dir(_PS_IMG_DIR_ . 'ets_abandonedcart')) {
            @mkdir(_PS_IMG_DIR_ . 'ets_abandonedcart', 0777);
        }
        if (!is_dir(_PS_IMG_DIR_ . 'ets_abandonedcart/img')) {
            @mkdir(_PS_IMG_DIR_ . 'ets_abandonedcart/img', 0777);
        }
        return _PS_IMG_DIR_ . 'ets_abandonedcart/img';
    }

    public static function getNbSentMailQueue($id_queue)
    {
        return (int)Db::getInstance()->getValue('SELECT send_count FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE `id_ets_abancart_email_queue` = ' . (int)$id_queue);
    }

    public static function getTotalOrderByIdShop($id_shop, $filter = null)
    {
        return (float)Db::getInstance()->getValue('
            SELECT SUM(o.total_paid_tax_incl) 
            FROM (
                SELECT total_paid_tax_incl 
                FROM `' . _DB_PREFIX_ . 'orders` a 
                INNER JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.id_order_state = a.current_state)
                INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` t ON (t.id_cart = a.id_cart)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = t.id_ets_abancart_reminder)
                LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
                WHERE a.id_shop=' . (int)$id_shop . ' 
                    AND t.id_cart > 0
                    AND os.paid = 1
                    AND t.delivered=1
                    AND (t.id_ets_abancart_reminder > 0 AND ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR t.id_ets_abancart_reminder = -1)
                    ' . ($filter !== null ? ' AND ' . $filter : '') . '
                GROUP BY a.id_order
            ) as o
        ');
    }

    public static function getNbOrderByIdShop($id_shop, $filter = null)
    {
        return (int)Db::getInstance()->getValue('
            SELECT COUNT(DISTINCT a.id_order) 
            FROM `' . _DB_PREFIX_ . 'orders` a 
            INNER JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.id_order_state = a.current_state)
            INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_tracking` t ON (t.id_cart = a.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = t.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
            WHERE a.id_shop=' . (int)$id_shop . ' 
                AND t.id_cart > 0
                AND os.paid = 1
                AND t.delivered=1
                AND (t.id_ets_abancart_reminder > 0 AND ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR t.id_ets_abancart_reminder = -1)
                ' . ($filter !== null ? ' AND ' . $filter : '') . '
        ');
    }

    public static function getMinYear()
    {
        return Db::getInstance()->getValue('SELECT YEAR(o.date_add) FROM `' . _DB_PREFIX_ . 'orders` o ORDER BY o.date_add ASC');
    }

    public static function getIdOrderByIdCart($id_cart)
    {
        return (int)Db::getInstance()->getValue('SELECT `id_order` FROM `' . _DB_PREFIX_ . 'orders`WHERE `id_cart` = ' . (int)$id_cart . Shop::addSqlRestriction());
    }

    public static function doSqlFilter($sql)
    {
        return Db::getInstance()->executeS($sql);
    }

    public static function updateTrackingDataIsRead($id_ets_abancart_reminder, $id_cart = 0, $id_customer = 0, $email = null)
    {
        if (!$id_ets_abancart_reminder || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;
        return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
            SET `read`=1, `date_upd`="' . date('Y-m-d H:i:s') . '" 
            WHERE `id_cart`' . ($id_cart > 0 ? '=' . (int)$id_cart : ' is NULL') . ' AND `id_customer`' . ($id_customer > 0 ? '=' . (int)$id_customer : ' is NULL') . ' AND `email`' . (trim($email) !== '' ? '=\'' . pSQL($email) . '\'' : ' is NULL') . ' AND `id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder
        );
    }

    public static function addCartRules($ids, $id_cart_rule)
    {
        if (Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "cart_rule_product_rule_group` (`id_cart_rule`, `quantity`) VALUES(" . (int)$id_cart_rule . ", " . (int)count($ids) . ")")) {
            $idProductRuleGroup = Db::getInstance()->getValue("SELECT id_product_rule_group FROM `" . _DB_PREFIX_ . "cart_rule_product_rule_group` WHERE `id_cart_rule`=" . (int)$id_cart_rule);
            if ($idProductRuleGroup && Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "cart_rule_product_rule` (`id_product_rule_group`, `type`) VALUES(" . (int)$idProductRuleGroup . ", 'products')")) {
                $idProductRule = Db::getInstance()->getValue("SELECT id_product_rule FROM `" . _DB_PREFIX_ . "cart_rule_product_rule` WHERE `id_product_rule_group`=" . (int)$idProductRuleGroup . " AND `type`='products'");
                if ($idProductRule) {
                    foreach ($ids as $idProduct) {
                        if ((int)$idProduct)
                            Db::getInstance()->execute("INSERT INTO `" . _DB_PREFIX_ . "cart_rule_product_rule_value` (id_product_rule, id_item) VALUES(" . (int)$idProductRule . "," . (int)$idProduct . ")");
                    }
                }
            }
        }
    }

    public static function getCombinationImages($id_product_attribute, $id_lang)
    {
        return Db::getInstance()->executeS('
				SELECT pai.`id_image`, pai.`id_product_attribute`, il.`legend`
				FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai
				LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (il.`id_image` = pai.`id_image`)
				LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = pai.`id_image`)
				WHERE pai.`id_product_attribute` = ' . (int)$id_product_attribute . ' AND il.`id_lang` = ' . (int)$id_lang . ' ORDER by i.`position` LIMIT 1'
        );
    }

    public static function getMetaByRewrite($url_rewrite)
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'meta_lang` WHERE url_rewrite ="' . pSQL($url_rewrite) . '"');
    }

    public static function getMetaByControllerModule($moduleName, $controller)
    {
        Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'meta` WHERE page ="module-' . pSQL($moduleName) . '-' . pSQL($controller) . '"');
    }

    public static function getMetaIdByControllerModule($moduleName, $controller)
    {
        return (int)Db::getInstance()->getValue('SELECT id_meta FROM `' . _DB_PREFIX_ . 'meta` WHERE page ="module-' . pSQL($moduleName) . '-' . pSQL($controller) . '"');
    }

    public static function isPageCachedEnabled()
    {
        return (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema =\'' . _DB_NAME_ . '\' AND table_name =\'`' . _DB_PREFIX_ . 'ets_pagecache_dynamic`\'');
    }

    public static function addModuleToPagecache($id)
    {
        return (int)Db::getInstance()->execute('
				INSERT INTO `' . _DB_PREFIX_ . 'ets_pagecache_dynamic`(`id_module`, `hook_name`, `empty_content`) 
				VALUES (\'' . (int)$id . '\', \'displayFooter\', 1) ON DUPLICATE KEY UPDATE `id_module` = ' . (int)$id . '
			');
    }

    public static function deleteModuleFromPagecache($id)
    {
        return (int)Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_pagecache_dynamic` WHERE `id_module` = ' . (int)$id);
    }

    public static function quickSort($list, $field = 'position')
    {
        $left = $right = array();
        if (count($list) <= 1 || trim($field) == '') {
            return $list;
        }
        $pivot_key = key($list);
        $pivot = array_shift($list);

        foreach ($list as $key => $val) {
            if ($val[$field] <= $pivot[$field]) {
                $left[$key] = $val;
            } elseif ($val[$field] > $pivot[$field]) {
                $right[$key] = $val;
            }
        }
        // recursive:
        return array_merge(self::quickSort($left, $field), array($pivot_key => $pivot), self::quickSort($right, $field));
    }

    public static function formatDateStr($date_str, $full = false)
    {
        $time = strtotime($date_str);
        $context = Context::getContext();
        $date_format = ($full ? $context->language->date_format_full : $context->language->date_format_lite);
        $date = date($date_format, $time);

        return $date;
    }

    public static function getCustomizationId($id_cart, $id_product)
    {
        return (int)Db::getInstance()->getValue('SELECT `id_customization` FROM `' . _DB_PREFIX_ . 'customization` WHERE `id_cart` = ' . (int)$id_cart . ' AND `id_product`=' . (int)$id_product);
    }
}