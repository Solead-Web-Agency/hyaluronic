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

class EtsAbancartTracking extends ObjectModel
{
    public $id_cart;
    public $id_customer;
    public $ip_address;
    public $email;
    public $id_shop;
    public $id_ets_abancart_reminder;
    public $date_add;
    public $date_upd;
    public $display_times;
    public $total_execute_times = 0;
    public $delivered = 0;
    public $read = 0;
    public $deleted = 0;
    public $customer_last_visit;

    public static $definition = array(
        'table' => 'ets_abancart_tracking',
        'primary' => 'id_ets_abancart_tracking',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail'),
            'ip_address' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_ets_abancart_reminder' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'display_times' => array('type' => self::TYPE_DATE),
            'total_execute_times' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'delivered' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'read' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'deleted' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'customer_last_visit' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public static function setDelete($id_ets_abancart_reminder = 0, $id_shop = null)
    {
        if ($id_ets_abancart_reminder > 0 && !Validate::isUnsignedInt($id_ets_abancart_reminder))
            return false;

        if ($id_shop == null)
            $id_shop = Context::getContext()->shop->id;

        return Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
                SET `deleted` = 1 
            WHERE id_shop = ' . (int)$id_shop . ($id_ets_abancart_reminder > 0 ? ' AND `id_ets_abancart_reminder`=' . (int)$id_ets_abancart_reminder : '')
        );
    }

    public static function trackingDiscount($tracking_id, $cart_rule_id, $use_same_cart = 0)
    {
        return Db::getInstance()->insert('ets_abancart_discount', ['id_ets_abancart_tracking' => $tracking_id, 'id_cart_rule' => $cart_rule_id, 'use_same_cart' => $use_same_cart], false, true, Db::INSERT_IGNORE);
    }

    public static function getDiscountByTrackingId($tracking_id)
    {
        if (!$tracking_id || !Validate::isUnsignedInt($tracking_id))
            return false;
        $dq = new DbQuery();
        $dq
            ->select('id_cart_rule')
            ->from('ets_abancart_discount')
            ->where('id_ets_abancart_tracking=' . (int)$tracking_id);
        return (int)Db::getInstance()->getValue($dq);
    }

    public static function itemExist($id_reminder = 0, $id_cart = 0, $id_shop = 0, $return = true, $is_leave_reminder = false)
    {
        if ($id_reminder && !Validate::isInt($id_reminder) ||
            $id_cart && !Validate::isInt($id_cart) ||
            $id_shop && !Validate::isInt($id_shop)
        ) {
            return false;
        }

        $dq = new DbQuery();
        $dq
            ->select('a.id_ets_abancart_tracking')
            ->from('ets_abancart_tracking', 'a');

        if ($id_reminder || $is_leave_reminder) {
            $dq->where('a.id_ets_abancart_reminder=' . (int)$id_reminder);
        }
        if ($id_cart) {
            $dq->where('a.id_cart=' . (int)$id_cart);
        }
        if ($id_shop) {
            $dq->where('a.id_shop=' . (int)$id_shop);
        }

        return $return ? (int)Db::getInstance()->getValue($dq) : Db::getInstance()->executeS($dq);
    }

    public static function getInstance($id_reminder, $id_cart, $id_shop, $is_leave_reminder = false)
    {
        $id = EtsAbancartTracking::itemExist($id_reminder, $id_cart, $id_shop, true, $is_leave_reminder);
        $tracking = new EtsAbancartTracking($id);
        return $tracking;
    }

    public static function getLogs($id_cart = 0, $id_customer = 0)
    {
        if (($id_cart <= 0 || !Validate::isUnsignedInt($id_cart)) && ($id_customer <= 0 || !Validate::isUnsignedInt($id_customer)))
            return false;
        $context = Context::getContext();
        $sql = '
            SELECT a.*, 
                IF(cr.id_cart_rule is NOT NULL, TIMESTAMPDIFF(DAY,cr.date_from, cr.date_to), 0) as `expiration_date`,
                IF(ac.campaign_type is NOT NULL,ac.campaign_type, \'leave\') `campaign_type`,
                IF(etl.id_ets_abancart_email_template is NOT NULL OR etl.id_ets_abancart_email_template, etl.name, NULL ) as `template_name`,
                arl.title `reminder_name`,
                d.id_cart_rule
            FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` a 
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_ets_abancart_tracking = a.id_ets_abancart_tracking)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder_lang` arl ON (arl.id_ets_abancart_reminder = ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ar.id_ets_abancart_campaign = ac.id_ets_abancart_campaign)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_email_template_lang` etl ON (etl.id_ets_abancart_email_template = ar.id_ets_abancart_email_template AND etl.id_lang = ' . (int)$context->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (cr.id_cart_rule = d.id_cart_rule)
            WHERE 1' . ($id_cart > 0 ? ' AND a.id_cart = ' . (int)$id_cart : '') . ($id_customer > 0 ? ' AND a.id_customer = ' . (int)$id_customer : '') . ' AND a.id_shop = ' . (int)$context->shop->id . '
        ';
        return Db::getInstance()->executeS($sql);
    }

    public static function reminderLogs($id)
    {
        $context = Context::getContext();
        $sql = '
            SELECT COUNT(a.id_cart) `total_cart`, COUNT(cus.id_customer) `total_customer`,COUNT(DISTINCT cr.id_cart_rule) `total_cart_rule`, SUM(IF(a.read is NOT NULL AND a.read > 0, 1, 0)) `total_read`, 0 `total_view`
            FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` a 
            INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_ets_abancart_tracking = a.id_ets_abancart_tracking)
            INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
            LEFT JOIN `' . _DB_PREFIX_ . 'cart` c ON (c.id_cart = a.id_cart)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` cus ON (cus.id_customer = a.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (cr.id_cart_rule = d.id_cart_rule)
            WHERE ar.id_ets_abancart_reminder = ' . (int)$id . ' AND a.id_shop = ' . (int)$context->shop->id . '
        ';
        return Db::getInstance()->getRow($sql);
    }

    public static function cartRuleValidity($id_cart, $id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('
            SELECT d.id_cart_rule 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_discount` d
            LEFT JOIN  `' . _DB_PREFIX_ . 'ets_abancart_tracking` at ON (at.id_ets_abancart_tracking = d.id_ets_abancart_tracking) 
            WHERE d.id_cart_rule=' . (int)$id_cart_rule . ' AND at.id_cart=' . (int)$id_cart
        );
    }

    public static function isVoucher($id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('SELECT d.id_cart_rule FROM `' . _DB_PREFIX_ . 'ets_abancart_discount` d WHERE d.id_cart_rule=' . (int)$id_cart_rule);
    }

    public static function getVoucherIsSameCart($id_cart_rule)
    {
        return (int)Db::getInstance()->getValue('
            SELECT d.use_same_cart 
            FROM `' . _DB_PREFIX_ . 'ets_abancart_discount` d 
            WHERE d.id_cart_rule=' . (int)$id_cart_rule
        );
    }

    public static function getVoucherNotIsSameCart($id_cart)
    {
        return (int)Db::getInstance()->getValue("
            SELECT ccr.id_cart_rule 
            FROM `" . _DB_PREFIX_ . "cart_cart_rule` ccr 
            WHERE id_cart=" . (int)$id_cart . " AND id_cart_rule IN (
                SELECT d.id_cart_rule 
                FROM `" . _DB_PREFIX_ . "ets_abancart_discount` d
                INNER JOIN `" . _DB_PREFIX_ . "cart_rule` c ON c.id_cart_rule=d.id_cart_rule
                WHERE d.use_same_cart < 1
            )
        ");
    }

    public static function hasCartRules($id_cart_rule = 0, $context = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        $sql = '
            SELECT ccr.id_cart_rule 
            FROM `' . _DB_PREFIX_ . 'cart_cart_rule` ccr
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_cart_rule = ccr.id_cart_rule)
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
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_cart_rule = cr.id_cart_rule) 
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
                ->innerJoin('ets_abancart_discount', 'd', 'd.id_cart_rule = ccr.id_cart_rule')
                ->where('ccr.id_cart=' . (int)$context->cart->id)
        );

    }

    public static function updateTrackingData($data, $where = '')
    {
        return Db::getInstance()->execute('
                    UPDATE `' . _DB_PREFIX_ . 'ets_abancart_tracking` 
                    SET 
                        ' . (string)$data . '
                    WHERE ' . (string)$where
        );
    }

    public static function deleteTrackingById($id)
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_email_queue` WHERE `id_ets_abancart_email_queue` = ' . (int)$id);
    }

    public static function getNbCartTracking($id_shop, $filter = null)
    {
        return (int)Db::getInstance()->getValue('
            SELECT COUNT(a.id_cart)
            FROM `' . _DB_PREFIX_ . 'cart` a
			LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = a.id_cart)
			WHERE a.id_shop=' . (int)$id_shop . ' AND (o.id_cart is NULL OR o.id_cart < 1)' . ($filter !== null ? ' AND ' . $filter : '') . '
        ');
    }

    public static function getAbancartValue($id_shop = null, $filter = null)
    {
        if ($id_shop == null)
            $id_shop = Context::getContext()->shop->id;
        $is_feature_active = Combination::isFeatureActive();
        $sql = '
            SELECT SUM((IFNULL(tmp.price, 0) + IFNULL(tmp.attribute_price, 0))* IFNULL(pcp.quantity, 0))
            FROM `' . _DB_PREFIX_ . 'cart` a
            LEFT JOIN `' . _DB_PREFIX_ . 'cart_product` pcp ON pcp.id_cart = a.id_cart
            LEFT JOIN (
                SELECT p.id_product
                    , pps.id_shop
                    , pps.`price`
                    , pps.`ecotax`
                    , ' . ($is_feature_active ? 'IFNULL(ppas.id_product_attribute, 0) id_product_attribute, ppas.`price` as attribute_price' : '0 as id_product_attribute, 0 as attribute_price') . '
                FROM `' . _DB_PREFIX_ . 'product` p
                 ' . ($is_feature_active ? '
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_shop` pps on (p.id_product = pps.id_product)
					LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa on (p.id_product = pa.id_product)
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` ppas on (pa.id_product_attribute = ppas.id_product_attribute)
                ' : '') . '
            ) as tmp ON (pcp.id_product = tmp.id_product AND pcp.id_product_attribute = tmp.id_product_attribute AND tmp.id_shop = a.id_shop)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = a.id_cart)
            WHERE a.id_shop = ' . (int)$id_shop . ' AND (o.id_cart is NULL OR o.id_cart < 1)' . ($filter !== null ? ' AND ' . $filter : '') . ';
        ';

        return (float)Db::getInstance()->getValue($sql);
    }

    public static function getNbGeneratedCode($id_shop = null, $filter = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }
        return (int)Db::getInstance()->getValue('
			SELECT COUNT(t.id_cart_rule) 
			FROM (
			    SELECT d.id_cart_rule 
			    FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` a 
			    INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_ets_abancart_tracking = a.id_ets_abancart_tracking)
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
                SELECT d.id_cart_rule 
                FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` a 
                INNER JOIN `' . _DB_PREFIX_ . 'ets_abancart_discount` d ON (d.id_ets_abancart_tracking = a.id_ets_abancart_tracking) 
                WHERE a.id_shop=' . (int)$id_shop . ($filter !== null ? ' AND ' . $filter : '') . '
            ) as t
			INNER JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON (cr.id_cart_rule = t.id_cart_rule)
			LEFT JOIN `' . _DB_PREFIX_ . 'cart_cart_rule` ccr ON (ccr.id_cart_rule = cr.id_cart_rule)
			LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_cart = ccr.id_cart)
			WHERE t.id_cart_rule is NOT NULL 
                AND ccr.id_cart_rule is NOT NULL 
                AND o.id_cart is NOT NULL 
		');
    }

    public static function getTotalCampaigns($id_shop = null, $filter = null)
    {
        if ($id_shop == null)
            $id_shop = Context::getContext()->shop->id;
        $dq = new DbQuery();
        $dq
            ->select('COUNT(DISTINCT b.id_ets_abancart_reminder)')
            ->from('ets_abancart_tracking', 'a')
            ->leftJoin('ets_abancart_reminder', 'b', 'b.id_ets_abancart_reminder = a.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_campaign', 'c', 'c.id_ets_abancart_campaign = b.id_ets_abancart_campaign')
            ->where('b.id_ets_abancart_reminder > 0')
            ->where('c.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->where('c.id_shop=' . (int)$id_shop);
        if ($filter !== null)
            $dq->where($filter);
        return (int)Db::getInstance()->getValue($dq);
    }

    public static function getTotalEmailReminders($id_shop = null, $filter = null)
    {
        $dq = new DbQuery();
        $dq
            ->select('COUNT(a.id_ets_abancart_tracking)')
            ->from('ets_abancart_tracking', 'a')
            ->leftJoin('ets_abancart_reminder', 'b', 'b.id_ets_abancart_reminder = a.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_campaign', 'c', 'c.id_ets_abancart_campaign = b.id_ets_abancart_campaign')
            ->where('b.id_ets_abancart_reminder > 0')
            ->where('c.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->where('c.id_shop=' . (int)$id_shop);

        if ($filter !== null)
            $dq->where($filter);

        return (int)Db::getInstance()->getValue($dq);
    }

    public static function getDataTrackingCampaigns($filter = null, $id_shop = null)
    {
        if (!$id_shop)
            $id_shop = Context::getContext()->shop->id;

        return Db::getInstance()->executeS('
				SELECT ac.campaign_type, 
				    SUM(a.total_execute_times) as `total_execute_times`,      
					SUM(IF(a.read = 1, 1, 0)) as `total_read`,
       				SUM(IF(a.delivered = 0, 1, 0)) as `total_failed`,
       				SUM(IF(a.delivered = 1, 1, 0)) as `total_success`,
       				0 as `total_view`,
       				SUM(IF(qu.id_ets_abancart_reminder > 0, 1, NULL)) as `total_queue`
				FROM `' . _DB_PREFIX_ . 'ets_abancart_tracking` a 
				LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_reminder` ar ON (ar.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
				LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_campaign` ac ON (ac.id_ets_abancart_campaign = ar.id_ets_abancart_campaign)
				LEFT JOIN `' . _DB_PREFIX_ . 'ets_abancart_email_queue` qu ON (qu.id_ets_abancart_reminder = a.id_ets_abancart_reminder)
				WHERE ar.id_ets_abancart_reminder > 0 
				    AND ac.id_ets_abancart_campaign > 0 
				    ' . ($filter !== null ? ' AND ' . $filter : '') . ' 
				    AND a.id_shop = ' . (int)$id_shop . '
				    AND (ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) . '\' OR ac.campaign_type=\'' . pSQL(EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) . '\')
				GROUP BY ac.campaign_type
			');
    }

    public static function getTotalTracking($id_ets_abancart_reminder = null)
    {
        return (int)Db::getInstance()->getValue("SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "ets_abancart_tracking` WHERE 1 " . ($id_ets_abancart_reminder > 0 ? " AND id_ets_abancart_reminder=" . (int)$id_ets_abancart_reminder : ""));
    }

    public static function cleanDisplayLog()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE `' . _DB_PREFIX_ . 'ets_abancart_mail_log`');
    }
}