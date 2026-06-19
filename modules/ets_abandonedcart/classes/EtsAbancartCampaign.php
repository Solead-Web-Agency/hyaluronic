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

class EtsAbancartCampaign extends ObjectModel
{
    const _CAMPAIGN_TYPE_ = 'email,popup,bar,cart,browser,customer,leave';

    //Campaign type
    const CAMPAIGN_TYPE_EMAIL = 'email';
    const CAMPAIGN_TYPE_CUSTOMER = 'customer';
    const CAMPAIGN_TYPE_POPUP = 'popup';
    const CAMPAIGN_TYPE_BAR = 'bar';
    const CAMPAIGN_TYPE_CART = 'cart';
    const CAMPAIGN_TYPE_BROWSER = 'browser';
    const CAMPAIGN_TYPE_LEAVE = 'leave';

    //Has applied voucher
    const APPLIED_VOUCHER_YES = 'yes';
    const APPLIED_VOUCHER_NO = 'no';
    const APPLIED_VOUCHER_BOTH = 'both';

    //Shopping cart
    const HAS_SHOPPING_CART_YES = 1;
    const HAS_SHOPPING_CART_NO = 0;
    const HAS_SHOPPING_CART_BOTH = 2;

    //Newsletter
    const CUSTOMER_NEWSLETTER_YES = 1;
    const CUSTOMER_NEWSLETTER_NO = 0;
    const CUSTOMER_NEWSLETTER_BOTH = 2;

    const CAMPAIGN_STATUS_DISABLED = 1;
    const CAMPAIGN_STATUS_A_WAITING = 2;
    const CAMPAIGN_STATUS_EXPIRED = 3;

    public $id_ets_abancart_campaign;
    public $id_shop;
    public $campaign_type;
    public $name;
    public $available_from;
    public $available_to;
    public $has_product_in_cart;
    public $min_total_cart;
    public $max_total_cart;
    public $customer_group;
    public $countries = array(0);
    public $languages = array(0);
    public $has_placed_orders;
    public $min_total_order;
    public $max_total_order;
    public $has_applied_voucher;
    public $is_all_country;
    public $is_all_lang;
    public $last_order_from;
    public $last_order_to;
    public $purchased_product;
    public $not_purchased_product;
    public $email_timing_option;
    public $enabled;
    public $deleted;
    public $date_add;
    public $newsletter;
    public static $instance = null;

    public static $definition = array(
        'table' => 'ets_abancart_campaign',
        'primary' => 'id_ets_abancart_campaign',
        'multilang' => true,
        'fields' => array(
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'campaign_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'available_from' => array('type' => self::TYPE_STRING, 'validate' => 'isDate'),
            'available_to' => array('type' => self::TYPE_STRING, 'validate' => 'isDate'),
            'has_product_in_cart' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'min_total_cart' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedFloat'),
            'max_total_cart' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedFloat'),
            'has_placed_orders' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'min_total_order' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedFloat'),
            'max_total_order' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedFloat'),
            'has_applied_voucher' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'is_all_country' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_all_lang' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'last_order_from' => array('type' => self::TYPE_STRING, 'validate' => 'isDate'),
            'last_order_to' => array('type' => self::TYPE_STRING, 'validate' => 'isDate'),
            'purchased_product' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'not_purchased_product' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'enabled' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'deleted' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'newsletter' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'email_timing_option' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            // Lang fields
            'name' => array('type' => self::TYPE_STRING, 'lang' => true, 'size' => 100, 'validate' => 'isString'),
        )
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        if ($this->id) {
            if (trim($this->campaign_type) !== 'customer'
                && ($group_ids = $this->getCustomerGroups())
            ) {
                $this->customer_group = $group_ids;
            }
            $this->countries = $this->is_all_country ? [0] : $this->getCountries();
            $this->languages = $this->is_all_lang ? [0] : $this->getLanguages();
        }
    }

    public function getCustomerGroups($ids = true)
    {
        if ($this->id) {
            $dq = new DbQuery();
            $dq
                ->from('ets_abancart_campaign_group')
                ->where('id_ets_abancart_campaign=' . (int)$this->id);
            if ($ids) {
                $dq
                    ->select('GROUP_CONCAT(id_group SEPARATOR ",")');
                $res = Db::getInstance()->getValue($dq);
                if ($res)
                    return explode(',', $res);
            } else {
                $dq
                    ->select('id_group');
                return Db::getInstance()->executeS($dq);
            }
        }
    }

    public function getCountries($ids = true)
    {
        if ($this->id) {
            $dq = new DbQuery();
            $dq
                ->from('ets_abancart_campaign_country')
                ->where('id_ets_abancart_campaign=' . (int)$this->id);
            if ($ids) {
                $dq
                    ->select('GROUP_CONCAT(id_country SEPARATOR \',\')');
                $res = Db::getInstance()->getValue($dq);
                if ($res)
                    return explode(',', $res);
            } else {
                $dq
                    ->select('id_country');
                return Db::getInstance()->executeS($dq);
            }
        }
    }

    public function getLanguages($ids = true)
    {
        if ($this->id) {
            $dq = new DbQuery();
            $dq
                ->from('ets_abancart_campaign_with_lang')
                ->where('id_ets_abancart_campaign=' . (int)$this->id);
            if ($ids) {
                $dq
                    ->select('GROUP_CONCAT(id_lang SEPARATOR \',\')');
                $res = Db::getInstance()->getValue($dq);
                if ($res)
                    return explode(',', $res);
            } else {
                $dq
                    ->select('id_lang');
                return Db::getInstance()->executeS($dq);
            }
        }
    }

    public function setCountryAndLanguage()
    {
        $this->is_all_country = !$this->countries || is_array($this->countries) && in_array(0, $this->countries) ? 1 : 0;
        $this->is_all_lang = !$this->languages || is_array($this->languages) && in_array(0, $this->languages) ? 1 : 0;
    }

    public function add($auto_date = true, $null_values = true)
    {
        if (!$null_values)
            $null_values = true;

        if (trim($this->has_applied_voucher) == '') {
            $this->has_applied_voucher = 'both';
        }

        $this->setCountryAndLanguage();
        if (parent::add($auto_date, $null_values)) {
            if ($this->campaign_type !== EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER && !$this->addCustomerGroup() ||
                !$this->addCountries() ||
                !$this->addLanguages()
            ) {
                return false;
            }
        }
        return true;
    }

    public function addCustomerGroup($beforeDelete = false)
    {
        if ($this->id
            && is_array($this->customer_group)
            && count($this->customer_group) > 0
            && (!$beforeDelete || $this->deleteCustomerGroup())
        ) {
            $values = [];
            foreach ($this->customer_group as $id_group) {
                if (trim($id_group) !== 'ALL' && Validate::isUnsignedInt($id_group))
                    $values[] = '(' . (int)$this->id . ', ' . (int)$id_group . ')';
            }
            if ($values)
                return (bool)Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_campaign_group`(id_ets_abancart_campaign, id_group) VALUES ' . implode(',', $values));
        }
        return true;
    }

    public function deleteCustomerGroup()
    {
        return (bool)Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign_group` WHERE id_ets_abancart_campaign = ' . (int)$this->id);
    }

    public function addCountries($beforeDelete = false)
    {
        if ($this->id
            && EtsAbancartTools::isArrayWithIds($this->countries)
            && count($this->countries) > 0
            && (!$beforeDelete || $this->deleteCountries())
        ) {
            if (!$this->is_all_country) {
                $values = [];
                foreach ($this->countries as $id_country) {
                    $values[] = '(' . (int)$this->id . ', ' . (int)$id_country . ')';
                }
                return (bool)Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_campaign_country`(id_ets_abancart_campaign, id_country) VALUES ' . implode(',', $values));
            }
        }
        return true;
    }

    public function deleteCountries()
    {
        return (bool)Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign_country` WHERE id_ets_abancart_campaign = ' . (int)$this->id);
    }

    public function addLanguages($beforeDelete = false)
    {
        if ($this->id
            && Validate::isArrayWithIds($this->languages)
            && count($this->languages) > 0
            && (!$beforeDelete || $this->deleteLanguages())
        ) {
            if (!$this->is_all_lang) {
                $values = [];
                foreach ($this->languages as $id_lang) {
                    $values[] = '(' . (int)$this->id . ', ' . (int)$id_lang . ')';
                }
                return (bool)Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang`(id_ets_abancart_campaign, id_lang) VALUES ' . implode(',', $values));
            }
        }
        return true;
    }

    public function deleteLanguages()
    {
        return (bool)Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign_with_lang` WHERE id_ets_abancart_campaign = ' . (int)$this->id);
    }

    public static function getStatusById($id_ets_abancart_campaign)
    {
        if (!$id_ets_abancart_campaign || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;
        return (int)Db::getInstance()->getValue('SELECT `enabled` FROM `' . _DB_PREFIX_ . 'ets_abancart_campaign` WHERE `id_ets_abancart_campaign`=' . (int)$id_ets_abancart_campaign);
    }

    public function delete()
    {
        $this->deleted = 1;
        if ($res = $this->update()) {
            $res &= $this->deleteIndex();
        }
        return $res;
    }

    public function deleteIndex()
    {
        $res = true;
        if ($this->campaign_type == self::CAMPAIGN_TYPE_EMAIL)
            $res &= EtsAbancartIndex::deleteIndex($this->id);
        if ($this->campaign_type == self::CAMPAIGN_TYPE_CUSTOMER)
            $res &= EtsAbancartIndexCustomer::deleteIndex($this->id);
        return $res;
    }

    public function update($null_values = true)
    {
        if (!$null_values)
            $null_values = true;

        if ($this->deleted)
            return parent::update($null_values);
        $this->setCountryAndLanguage();
        if ($res = parent::update($null_values)) {
            if (!in_array($this->campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL, EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER]) && (int)$this->has_product_in_cart !== EtsAbancartCampaign::HAS_SHOPPING_CART_YES) {
                EtsAbancartReminder::setNoVoucher($this->id);
            }
            if ($this->campaign_type !== EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER && !$this->addCustomerGroup(true) || !$this->addCountries(true) || !$this->addLanguages(true))
                return false;
            $res &= $this->deleteIndex();
        }
        return $res;
    }

    /**
     * @param bool $active
     * @param Context $context
     */
    public static function getCampaignsFrontEnd($context = null, $exclude_cookie = true)
    {
        if ($context == null)
            $context = Context::getContext();

        if ($context->cart->id > 0 && Order::getOrderByCartId($context->cart->id))
            return false;

        $idCustomer = isset($context->customer) && $context->customer instanceof Customer && $context->customer->id > 0 ? $context->customer->id : 0;
        $group = new Group($idCustomer ? $context->customer->id_default_group : Group::getCurrent()->id);
        $total_cart = Tools::convertPrice($context->cart->getOrderTotal(!$group->price_display_method), null, false);
        $has_applied_voucher = ($cart_rules = $context->cart->getCartRules()) && is_array($cart_rules) && count($cart_rules) > 0 ? 1 : 0;
        $current_date = date('Y-m-d');
        $productInCart = count($context->cart->getProducts()) > 0;
        $dq = new DbQuery();
        $dq
            ->select('ac.id_ets_abancart_campaign')
            ->from('ets_abancart_campaign', 'ac')
            ->leftJoin('ets_abancart_campaign_country', 'cc', 'cc.id_ets_abancart_campaign = ac.id_ets_abancart_campaign')
            ->leftJoin('ets_abancart_campaign_with_lang', 'cl', 'cl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND cl.id_lang=' . (int)$context->language->id)
            ->leftJoin('ets_abancart_campaign_group', 'acg', 'ac.id_ets_abancart_campaign = acg.id_ets_abancart_campaign')
            ->leftJoin('group_shop', 'gs', 'gs.id_group = acg.id_group AND gs.id_shop = ' . (int)$context->shop->id)
            ->where('ac.id_shop = ' . (int)$context->shop->id)
            ->where('ac.enabled = 1 AND ac.deleted = 0')
            ->where('IF(ac.is_all_lang != 1, cl.id_ets_abancart_campaign is NOT NULL, 1)')
            ->where('ac.campaign_type != \'' . pSQL(self::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->where('ac.campaign_type != \'' . pSQL(self::CAMPAIGN_TYPE_CUSTOMER) . '\'')
            ->where('IF(ac.min_total_cart is NOT NULL AND ac.has_product_in_cart = 1, ac.min_total_cart <= ' . (float)$total_cart . ', 1) AND IF(ac.max_total_cart is NOT NULL AND ac.has_product_in_cart = 1, ac.max_total_cart >= ' . (float)$total_cart . ', 1)')
            ->where('IF(ac.available_from is NOT NULL, ac.available_from <= \'' . pSQL($current_date) . '\', 1) AND IF(ac.available_to is NOT NULL, ac.available_to >= \'' . pSQL($current_date) . '\', 1)')
            ->where('IF(ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_BOTH) . '\' OR (ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_YES) . '\' AND ' . (int)$has_applied_voucher . ' > 0) OR (ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_NO) . '\' AND ' . (int)$has_applied_voucher . ' = 0), 1, 0)')
            ->where('IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ', ' . (int)$productInCart . ', 1)')
            ->groupBy('ac.id_ets_abancart_campaign');

        if ($idCustomer) {
            $dq
                ->leftJoin('customer_group', 'cg', 'cg.id_group =  gs.id_group AND cg.id_customer=' . (int)$idCustomer)
                ->leftJoin('address', 'a', 'a.id_country = cc.id_country AND a.id_customer=' . (int)$idCustomer)
                ->where('cg.id_group is NOT NULL')
                ->where('IF(ac.is_all_country != 1, a.id_country > 0 OR cc.id_country = -1, 1)');
        } else {
            $dq
                ->where('acg.id_group = ' . (int)$group->id)
                ->where('ac.is_all_country = 1 OR cc.id_country = -1');
        }

        $campaigns = Db::getInstance()->executeS($dq);
        $ids = [];
        if ($campaigns)
            foreach ($campaigns as $campaign)
                $ids[] = (int)$campaign['id_ets_abancart_campaign'];

        return EtsAbancartReminder::getSQLReminders($ids, $context, $exclude_cookie);
    }

    public static function isValid($id_ets_abancart_campaign, $context = null)
    {
        if (!$id_ets_abancart_campaign || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;
        if ($context == null)
            $context = Context::getContext();

        if ($context->cart->id > 0 && Order::getOrderByCartId($context->cart->id))
            return false;

        $idCustomer = isset($context->customer) && $context->customer instanceof Customer && $context->customer->id > 0 ? $context->customer->id : 0;
        $group = new Group($idCustomer ? $context->customer->id_default_group : Group::getCurrent()->id);
        $total_cart = Tools::convertPrice($context->cart->getOrderTotal(!$group->price_display_method), null, false);
        $has_applied_voucher = ($cart_rules = $context->cart->getCartRules()) && is_array($cart_rules) && count($cart_rules) > 0 ? 1 : 0;
        $current_date = date('Y-m-d');
        $productInCart = count($context->cart->getProducts()) > 0;
        $dq = new DbQuery();
        $dq
            ->select('ac.id_ets_abancart_campaign')
            ->from('ets_abancart_campaign', 'ac')
            ->leftJoin('ets_abancart_campaign_country', 'cc', 'cc.id_ets_abancart_campaign = ac.id_ets_abancart_campaign')
            ->leftJoin('ets_abancart_campaign_with_lang', 'cl', 'cl.id_ets_abancart_campaign = ac.id_ets_abancart_campaign AND cl.id_lang=' . (int)$context->language->id)
            ->leftJoin('ets_abancart_campaign_group', 'acg', 'ac.id_ets_abancart_campaign = acg.id_ets_abancart_campaign')
            ->leftJoin('group_shop', 'gs', 'gs.id_group = acg.id_group AND gs.id_shop = ' . (int)$context->shop->id)
            ->where('ac.id_shop = ' . (int)$context->shop->id)
            ->where('ac.enabled = 1 AND ac.deleted = 0')
            ->where('IF(ac.is_all_lang != 1, cl.id_ets_abancart_campaign is NOT NULL, 1)')
            ->where('ac.campaign_type != \'' . pSQL(self::CAMPAIGN_TYPE_EMAIL) . '\'')
            ->where('ac.campaign_type != \'' . pSQL(self::CAMPAIGN_TYPE_CUSTOMER) . '\'')
            ->where('IF(ac.min_total_cart is NOT NULL AND ac.has_product_in_cart = 1, ac.min_total_cart <= ' . (float)$total_cart . ', 1) AND IF(ac.max_total_cart is NOT NULL AND ac.has_product_in_cart = 1, ac.max_total_cart >= ' . (float)$total_cart . ', 1)')
            ->where('IF(ac.available_from is NOT NULL, ac.available_from <= \'' . pSQL($current_date) . '\', 1) AND IF(ac.available_to is NOT NULL, ac.available_to >= \'' . pSQL($current_date) . '\', 1)')
            ->where('IF(ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_BOTH) . '\' OR (ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_YES) . '\' AND ' . (int)$has_applied_voucher . ' > 0) OR (ac.has_applied_voucher = \'' . pSQL(self::APPLIED_VOUCHER_NO) . '\' AND ' . (int)$has_applied_voucher . ' = 0), 1, 0)')
            ->where('IF(ac.has_product_in_cart = ' . EtsAbancartCampaign::HAS_SHOPPING_CART_YES . ', ' . (int)$productInCart . ', 1)')
            ->where('ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign);

        if ($idCustomer) {
            $dq
                ->leftJoin('customer_group', 'cg', 'cg.id_group =  gs.id_group AND cg.id_customer=' . (int)$idCustomer)
                ->leftJoin('address', 'a', 'a.id_country = cc.id_country AND a.id_customer=' . (int)$idCustomer)
                ->where('cg.id_group is NOT NULL')
                ->where('IF(ac.is_all_country != 1, a.id_country > 0 OR cc.id_country = -1, 1)');
        } else {
            $dq
                ->where('acg.id_group = ' . (int)$group->id)
                ->where('ac.is_all_country = 1 OR cc.id_country = -1');
        }

        return Db::getInstance()->getValue($dq);
    }

    public static function nbReminders($id_ets_abancart_campaign)
    {
        if (!$id_ets_abancart_campaign ||
            !Validate::isUnsignedInt($id_ets_abancart_campaign)
        ) {
            return 0;
        }
        $dq = new DbQuery();
        $dq
            ->select('COUNT(ar.id_ets_abancart_reminder)')
            ->from('ets_abancart_campaign', 'ac')
            ->leftJoin('ets_abancart_reminder', 'ar', 'ar.id_ets_abancart_campaign=ac.id_ets_abancart_campaign')
            ->where('ac.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign);

        return (int)Db::getInstance()->getValue($dq);
    }

    public static function getCampaigns(Context $context = null)
    {
        if (!$context)
            $context = Context::getContext();
        return Db::getInstance()->executeS(
            (new DbQuery())
                ->select('a.*, b.*')
                ->from('ets_abancart_campaign', 'a')
                ->leftJoin('ets_abancart_campaign_lang', 'b', 'a.id_ets_abancart_campaign = b.id_ets_abancart_campaign AND b.id_lang=' . (int)$context->language->id)
                ->where('id_shop=' . (int)$context->shop->id)
        );
    }

    public static function findProducts($query, $excludeIds, $excludePackItself = false, $excludeVirtuals = 0, $exclude_packs = 0, Context $context = null)
    {
        if ($excludeIds && !Validate::isArrayWithIds($excludeIds)) {
            return false;
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$query or $query == '' or Tools::strlen($query) < 1) {
            die();
        }
        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }
        $sql = '
            SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
            FROM `' . _DB_PREFIX_ . 'product` p
            ' . Shop::addSqlAssociation('product', 'p') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int)$context->language->id . Shop::addSqlRestrictionOnLang('pl') . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int)$context->shop->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int)$context->language->id . ')
            WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')' .
            (!empty($excludeIds) ? ' AND p.id_product NOT IN (' . implode(',', $excludeIds) . ') ' : ' ') .
            (!empty($excludePackItself) ? ' AND p.id_product <> ' . $excludePackItself . ' ' : ' ') .
            ($excludeVirtuals ? 'AND NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'product_download` pd WHERE (pd.id_product = p.id_product))' : '') .
            ($exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '') .
            ' GROUP BY p.id_product
        ';
        return Db::getInstance()->executeS($sql);
    }

    public static function getCampaignGroup($id_campaign, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        return Db::getInstance()->executeS("
            SELECT gl.*,g.* FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_group` acg 
            JOIN `" . _DB_PREFIX_ . "group` g ON (acg.id_group=g.id_group)
            JOIN `" . _DB_PREFIX_ . "group_lang` gl ON (g.id_group=gl.id_group AND id_lang=" . (int)$id_lang . ")
            WHERE acg.id_ets_abancart_campaign=" . (int)$id_campaign);
    }

    public static function getCampaignCountries($id_campaign, $get_all_country = false, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }
        if ($get_all_country) {
            return Country::getCountries($id_lang);
        }

        $countries = Db::getInstance()->executeS("
            SELECT c.*,cl.* FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_country` acc 
            JOIN `" . _DB_PREFIX_ . "country` c ON (acc.id_country = c.id_country) 
            JOIN `" . _DB_PREFIX_ . "country_lang` cl ON (c.id_country = cl.id_country AND cl.id_lang) 
            WHERE acc.id_ets_abancart_campaign=" . (int)$id_campaign . " GROUP BY c.id_country");
        if (!$countries && Db::getInstance()->getRow("SELECT * FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_country` WHERE id_country=-1 AND id_ets_abancart_campaign=" . (int)$id_campaign)) {
            return 'unknown';
        }
        return $countries;
    }

    public static function getCampaignLanguages($id_campaign, $get_all_lang = false)
    {
        if ($get_all_lang) {
            return Language::getLanguages(false);
        }
        return Db::getInstance()->executeS("
            SELECT acl.*,l.* FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_with_lang` acl 
            JOIN `" . _DB_PREFIX_ . "lang` l ON (acl.id_lang = l.id_lang) 
            WHERE acl.id_ets_abancart_campaign=" . (int)$id_campaign . " GROUP BY l.id_lang");
    }

    public static function getEmailSent($id_ets_abancart_campaign, $limit = 0, $startDate = null, $endDate = null, $context = null)
    {
        if (!$id_ets_abancart_campaign || !Validate::isUnsignedInt($id_ets_abancart_campaign))
            return false;

        if ($context == null)
            $context = Context::getContext();
        $campaign = new EtsAbancartCampaign($id_ets_abancart_campaign);
        $dq = new DbQuery();

        if (in_array($campaign->campaign_type, [EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER, EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL])) {
            $dq
                ->select('customer.firstname, customer.lastname, customer.id_customer');
        }
        $dq->select('act.*
            , act.id_customer as id_customer_tracking
            , arl.title
            , ac.campaign_type
        ');
        $dq
            ->from('ets_abancart_tracking', 'act')
            ->leftJoin('ets_abancart_reminder', 'ar', 'act.id_ets_abancart_reminder=ar.id_ets_abancart_reminder')
            ->leftJoin('ets_abancart_reminder_lang', 'arl', 'arl.id_ets_abancart_reminder=ar.id_ets_abancart_reminder AND arl.id_lang=' . (int)$context->language->id)
            ->leftJoin('ets_abancart_campaign', 'ac', 'ar.id_ets_abancart_campaign=ac.id_ets_abancart_campaign')
            ->where('ar.id_ets_abancart_campaign=' . (int)$id_ets_abancart_campaign);
        if ($campaign->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_CUSTOMER) {
            $dq
                ->leftJoin('customer', 'customer', ' act.id_customer = customer.id_customer');
        } elseif ($campaign->campaign_type == EtsAbancartCampaign::CAMPAIGN_TYPE_EMAIL) {
            $dq
                ->leftJoin('cart', 'cart', 'act.id_cart=cart.id_cart')
                ->leftJoin('customer', 'customer', 'cart.id_customer = customer.id_customer')
                ->where('cart.id_cart > 0');
        }
        if ($startDate && Validate::isDate($startDate) && $endDate && Validate::isDate($endDate)) {
            $dq
                ->where('act.date_add >= "' . date('Y-m-d 00:00:00', strtotime($startDate)) . '"')
                ->where('act.date_add <= "' . date('Y-m-d 23:59:59', strtotime($endDate)) . '"');
        }
        if ($limit)
            $dq->limit($limit);

        return Db::getInstance()->executeS($dq);
    }

    public static function getCountryIdsOfCampaign($id_campaign)
    {
        return Db::getInstance()->executeS("SELECT id_country FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_country` WHERE id_ets_abancart_campaign=" . (int)$id_campaign);
    }

    public static function getLangIdsOfCampaign($id_campaign)
    {
        return Db::getInstance()->executeS("SELECT id_lang FROM `" . _DB_PREFIX_ . "ets_abancart_campaign_with_lang` WHERE id_ets_abancart_campaign=" . (int)$id_campaign);
    }
}