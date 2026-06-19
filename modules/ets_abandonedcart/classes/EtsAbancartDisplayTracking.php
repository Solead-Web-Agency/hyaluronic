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

class EtsAbancartDisplayTracking extends ObjectModel
{
    public $id_ets_abancart_reminder;
    public $id_shop;
    public $day;
    public $month;
    public $year;
    public $number_of_displayed = 0;

    public static $definition = array(
        'table' => 'ets_abancart_display_tracking',
        'primary' => 'id_ets_abancart_display_tracking',
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_ets_abancart_reminder' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'day' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'month' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'year' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'number_of_displayed' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
        )
    );

    public static function getDataTrackingCampaigns($filter, $id_shop = null)
    {
        if ($id_shop == null)
            $id_shop = Context::getContext()->shop->id;

        return Db::getInstance()->executeS('
				SELECT IF(a.id_ets_abancart_reminder > 0, ac.campaign_type, \'leave\') `campaign_type`
				     , SUM(a.`number_of_displayed`) as `total_execute_times`
				FROM `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` a 
				LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
				LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
				WHERE IF(a.id_ets_abancart_reminder > 0, ar.id_ets_abancart_reminder > 0 AND ac.id_ets_abancart_campaign > 0, IF(a.id_ets_abancart_reminder=0, 1, 0)) 
				    ' . ($filter !== null ? ' AND ' . $filter : '') . ' 
				    AND a.id_shop = ' . (int)$id_shop . '
				    AND ac.campaign_type != \'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' 
				    AND ac.campaign_type != \'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\'
				GROUP BY ac.campaign_type
			');
    }

    public static function filterId($id_ets_abancart_reminder, $context = null)
    {
        if ($context == null)
            $context = Context::getContext();

        return (int)Db::getInstance()->getValue('
            SELECT `id_ets_abancart_display_tracking` 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` 
            WHERE `id_shop`=' . (int)$context->shop->id . ' 
                AND `day`=' . (int)date('d') . ' 
                AND `month`=' . (int)date('m') . ' 
                AND `year`=' . (int)date('Y') . '
                AND `id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder . '
        ');
    }

    public static function saveData($id_ets_abancart_reminder, $id_ets_abancart_display_tracking = null, $reminderIsRun = false, $context = null)
    {
        if (trim($id_ets_abancart_reminder) !== '' && !Validate::isUnsignedInt($id_ets_abancart_reminder) || trim($id_ets_abancart_display_tracking) !== '' && !Validate::isUnsignedInt($id_ets_abancart_display_tracking))
            return 0;
        if ($context == null)
            $context = Context::getContext();
        $exec = Db::getInstance()->execute('
            INSERT IGNORE INTO `' . _DB_PREFIX_ . 'ets_abancart_display_tracking`( 
                `id_ets_abancart_display_tracking`,
                `id_ets_abancart_reminder`, 
                `id_shop`,                 
                `day`, 
                `month`, 
                `year`, 
                `number_of_displayed`
            ) 
            VALUES (
                ' . ($id_ets_abancart_display_tracking !== null ? (int)$id_ets_abancart_display_tracking : 'NULL') . ',
                ' . (int)$id_ets_abancart_reminder . ',
                ' . (int)$context->shop->id . ',
                ' . (int)date('d') . ',
                ' . (int)date('m') . ',
                ' . (int)date('Y') . ',
                1
            )' . (!$reminderIsRun ? ' ON DUPLICATE KEY UPDATE  `number_of_displayed` = `number_of_displayed` + 1' : '') . '
        ');

        return $exec ? ($id_ets_abancart_display_tracking ?: (int)Db::getInstance()->insert_ID()) : 0;
    }

    public static function setVoucher($id_ets_abancart_display_tracking, $id_cart, $id_cart_rule, $use_same_cart = 0)
    {
        if (!$id_ets_abancart_display_tracking || !Validate::isUnsignedInt($id_ets_abancart_display_tracking) || !$id_cart_rule || !Validate::isUnsignedInt($id_cart_rule) || !$id_cart || !Validate::isUnsignedInt($id_cart))
            return false;
        return Db::getInstance()->insert('ets_abancart_discount_display_tracking',
            [
                'id_ets_abancart_display_tracking' => $id_ets_abancart_display_tracking,
                'id_cart_rule' => $id_cart_rule,
                'id_cart' => $id_cart,
                'use_same_cart' => $use_same_cart
            ]
            , false
            , true
            , Db::INSERT_IGNORE
        );
    }

    public static function getVoucher($id_ets_abancart_display_tracking, $id_cart)
    {
        if (!$id_ets_abancart_display_tracking || !Validate::isUnsignedInt($id_ets_abancart_display_tracking) || !$id_cart || !Validate::isUnsignedInt($id_cart))
            return false;
        $dq = new DbQuery();
        $dq
            ->select('id_cart_rule')
            ->from('ets_abancart_discount_display_tracking')
            ->where('id_ets_abancart_display_tracking=' . (int)$id_ets_abancart_display_tracking)
            ->where('id_cart=' . (int)$id_cart);
        return (int)Db::getInstance()->getValue($dq);
    }

    public static function getNbGeneratedCode($id_shop = null, $filter = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }
        return (int)Db::getInstance()->getValue('
			SELECT COUNT(t.id_cart_rule) 
			FROM (
			    SELECT d.id_cart_rule FROM `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` a 
			    INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d ON (d.id_ets_abancart_display_tracking = a.id_ets_abancart_display_tracking)
			    WHERE a.id_shop=' . (int)$id_shop . ($filter !== null ? ' AND ' . $filter : '') . '
            ) as t
			INNER JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (cr.id_cart_rule = t.id_cart_rule)
		');
    }

    public static function getNbGeneratedCodeUsed($id_shop = null, $filter = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }
        return (int)Db::getInstance()->getValue('
            SELECT COUNT(t.id_cart_rule) 
            FROM (
                SELECT d.id_cart_rule FROM `' . _DB_PREFIX_ . 'ets_abancart_display_tracking` a 
                INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d ON (d.id_ets_abancart_display_tracking = a.id_ets_abancart_display_tracking) 
                WHERE a.id_shop=' . (int)$id_shop . ($filter !== null ? ' AND ' . $filter : '') . '
            ) as t
			INNER JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (cr.id_cart_rule = t.id_cart_rule)
			LEFT JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` ccr ON (ccr.id_cart_rule = cr.id_cart_rule)
			LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = ccr.id_cart)
			WHERE t.id_cart_rule is NOT NULL AND ccr.id_cart_rule is NOT NULL AND o.id_cart is NOT NULL 
		');
    }

    public static function cartRuleValidity($id_cart, $id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('
            SELECT d.id_cart_rule 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d 
            WHERE d.id_cart_rule=' . (int)$id_cart_rule . ' AND d.id_cart=' . (int)$id_cart
        );
    }

    public static function hasCartRules($id_cart_rule = 0, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $sql = '
            SELECT ccr.id_cart_rule FROM `' . _DB_PREFIX_ . 'cart_cart_rule` ccr
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d ON (d.id_cart_rule = ccr.id_cart_rule)
            WHERE d.id_cart_rule is NOT NULL' . ($id_cart_rule ? ' AND ccr.id_cart_rule = ' . (int)$id_cart_rule : '') . '  AND ccr.id_cart = ' . (int)$context->cart->id . '
        ';
        return (int)Db::getInstance()->getValue($sql);
    }

    // Check voucher by abandoned cart:
    public static function isCartRuleUsed($id_cart_rule)
    {
        return Db::getInstance()->getValue('
            SELECT cr.id_cart_rule
            FROM `' . _DB_PREFIX_ . 'cart_rule` cr
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d ON (d.id_cart_rule = cr.id_cart_rule)
            WHERE cr.active = 1 AND d.id_cart_rule is NOT NULL AND cr.id_cart_rule = ' . (int)$id_cart_rule . '
        ');
    }

    public static function onCartRule($context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        return (int)Db::getInstance()->getValue(
            (new DbQuery())
                ->select('COUNT(*)')
                ->from('cart_cart_rule', 'ccr')
                ->innerJoin('ets_abancart_discount_display_tracking', 'd', 'd.id_cart_rule = ccr.id_cart_rule')
                ->where('ccr.id_cart=' . (int)$context->cart->id)
        );
    }

    public static function getVoucherIsSameCart($id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('SELECT d.use_same_cart FROM `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d WHERE d.id_cart_rule=' . (int)$id_cart_rule);
    }

    public static function isVoucher($id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('SELECT d.id_cart_rule FROM `' . _DB_PREFIX_ . 'ets_abancart_discount_display_tracking` d WHERE d.id_cart_rule=' . (int)$id_cart_rule);
    }

    public static function getVoucherNotIsSameCart($id_cart)
    {
        return (int)Db::getInstance()->getValue("
            SELECT ccr.id_cart_rule 
            FROM `" . _DB_PREFIX_ . "cart_cart_rule` ccr 
            WHERE id_cart=" . (int)$id_cart . " AND id_cart_rule IN (
                SELECT d.id_cart_rule FROM `" . _DB_PREFIX_ . "ets_abancart_discount_display_tracking` d
                INNER JOIN `" . _DB_PREFIX_ . "cart_rule` c ON c.id_cart_rule=d.id_cart_rule
                WHERE d.use_same_cart < 1
            )
        ");
    }

    public static function writeLog($id_ets_abancart_display_tracking, $id_ets_abancart_reminder, $id_cart_rule = 0, $redisplay = 0, $closed = 0, $context = null)
    {
        if (trim($id_ets_abancart_display_tracking) == '' || !Validate::isUnsignedInt($id_ets_abancart_display_tracking) || trim($id_ets_abancart_reminder) == '' || !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;
        if ($context == null)
            $context = Context::getContext();

        if ($id_ets_abancart_display_tracking <= 0)
            $id_ets_abancart_display_tracking = EtsAbancartDisplayTracking::filterId($id_ets_abancart_reminder, $context);

        $isGuest = !(isset($context->customer) && $context->customer->id > 0 && $context->customer->isLogged());
        if ($isGuest && (int)$context->cookie->id_guest <= 0)
            return false;

        $command = 'INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_display_log`(
                `id_ets_abancart_display_tracking`
                , `id_shop`
                , `id_customer`
                , `id_guest`
                , `id_cart_rule`
                , `id_ets_abancart_reminder`
                , `customer_name`
                , `email`
                , `display_time`
                , `closed_time`
                , `last_display_time`
            ) VALUES (
                ' . (int)$id_ets_abancart_display_tracking . '
                , ' . (int)$context->shop->id . '
                , ' . (int)$context->customer->id . '
                , ' . (int)$context->cookie->id_guest . '
                , ' . ($id_cart_rule > 0 ? (int)$id_cart_rule : 'NULL') . '
                , ' . (int)$id_ets_abancart_reminder . '
                , ' . ($isGuest ? 'NULL' : '\'' . pSQL($context->customer->firstname . ' ' . $context->customer->lastname) . '\'') . '
                , ' . ($isGuest ? 'NULL' : '\'' . pSQL($context->customer->email) . '\'') . '
                , 1
                , 0
                , \'' . pSQL(date('Y-m-d H:i:s')) . '\'
            )
        ';
        if ($redisplay > 0 || $closed > 0) {
            $command .= ' ON DUPLICATE KEY UPDATE ' . ($redisplay > 0 ? '`last_display_time`=\'' . pSQL(date('Y-m-d H:i:s')) . '\', `display_time`=`display_time`+1' : '') . ($closed > 0 ? '`closed_time`=`closed_time`+1' : '');
        }
        return Db::getInstance()->execute($command);
    }

    public static function cleanDisplayLog()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ets_abancart_display_log`');
    }
}