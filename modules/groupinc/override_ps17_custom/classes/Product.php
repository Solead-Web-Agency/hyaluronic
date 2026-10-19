<?php
/**
* Price increment/reduction by groups, categories and more
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate
*  @copyright 2022 idnovate
*  @license   See above
*/

if (!class_exists('Product', false)) {
class Product extends ProductCore
{
    public static function priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
        $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, &$specific_price, $use_group_reduction,
        $id_customer = 0, $use_customer_price = true, $id_cart = 0, $real_quantity = 0, $id_customization = 0)
    {
        if (!Module::isEnabled('groupinc')) {
            return parent::priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
            $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, $specific_price, $use_group_reduction,
            $id_customer, $use_customer_price, $id_cart, $real_quantity, $id_customization);
        }
        $cache_id = (int)$id_product.'-'.(int)$id_shop.'-'.(int)$id_currency.'-'.(int)$id_country.'-'.$id_state.'-'.$zipcode.'-'.(int)$id_group.
                '-'.(int)$quantity.'-'.(int)$id_product_attribute.'-'.(int)$id_customization.
                '-'.(int)$with_ecotax.'-'.(int)$id_customer.'-'.(int)$use_group_reduction.'-'.(int)$id_cart.'-'.(int)$real_quantity.
                '-'.($only_reduc?'1':'0').'-'.($use_reduc?'1':'0').'-'.($use_tax?'1':'0').'-'.(int)$decimals;

        if (isset(self::$_prices[$cache_id])) {
            if (isset($specific_price['price']) && $specific_price['price'] > 0) {
                $specific_price['price'] = self::$_prices[$cache_id];
            }

            if (isset(self::$_prices['specific_price'])) {
                $specific_price = self::$_prices['specific_price'];
            }
            return self::$_prices[$cache_id];
        }

        $price = false;
        Hook::exec('actionProductPriceCalculation', array(
            'id_shop' => &$id_shop,
            'id_product' => &$id_product,
            'id_product_attribute' => &$id_product_attribute,
            'id_country' => &$id_country,
            'id_state' => &$id_state,
            'zipcode' => &$zipcode,
            'id_currency' => &$id_currency,
            'id_group' => &$id_group,
            'quantity' => &$quantity,
            'use_tax' => &$use_tax,
            'decimals' => &$decimals,
            'only_reduc' => &$only_reduc,
            'use_reduc' => &$use_reduc,
            'with_ecotax' => &$with_ecotax,
            'specific_price' => &$specific_price,
            'use_group_reduction' => &$use_group_reduction,
            'id_customer' => &$id_customer,
            'use_customer_price' => &$use_customer_price,
            'id_cart' => &$id_cart,
            'real_quantity' => &$real_quantity,
            'id_customization' => &$id_customization,
            'price' => &$price,
        ));

        self::$_prices['specific_price'] = $specific_price;

        if ($price != false) {
            self::$_prices[$cache_id] = $price;
            return self::$_prices[$cache_id];
        } else {
            return parent::priceCalculation($id_shop, $id_product, $id_product_attribute, $id_country, $id_state, $zipcode, $id_currency,
                $id_group, $quantity, $use_tax, $decimals, $only_reduc, $use_reduc, $with_ecotax, $specific_price, $use_group_reduction,
                $id_customer, $use_customer_price, $id_cart, $real_quantity, $id_customization);
        }
    }

    public static function getPricesDrop($id_lang, $page_number = 0, $nb_products = 10, $count = false,
        $order_by = null, $order_way = null, $beginning = false, $ending = false, Context $context = null)
    {
        if (!Module::isEnabled('groupinc')) {
            return parent::getPricesDrop($id_lang, $page_number, $nb_products, $count, $order_by, $order_way, $beginning, $ending, $context);
        }
        if (!Validate::isBool($count)) {
            die(Tools::displayError());
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if ($page_number < 1) {
            $page_number = 1;
        }
        if ($nb_products < 1) {
            $nb_products = 10;
        }
        if (empty($order_by) || $order_by == 'position') {
            $order_by = 'price';
        }
        if (empty($order_way)) {
            $order_way = 'DESC';
        }
        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'product_shop';
        } elseif ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }
        if (!Validate::isOrderBy($order_by) || !Validate::isOrderWay($order_way)) {
            die(Tools::displayError());
        }
        $current_date = date('Y-m-d H:i:00');
        $ids_product = Product::_getProductIdByDate((!$beginning ? $current_date : $beginning), (!$ending ? $current_date : $ending), $context);
        $tab_id_product = array();
        foreach ($ids_product as $product) {
            if (is_array($product)) {
                $tab_id_product[] = (int)$product['id_product'];
            } else {
                $tab_id_product[] = (int)$product;
            }
        }
        include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
        $groupinc = new GroupincConfiguration();
        $context = Context::getContext();
        $id_shop = $context->cart->id_shop;
        $id_currency = $context->cart->id_currency;
        $id_customer = $context->cart->id_customer;
        $id_address_delivery = $context->cart->id_address_delivery;
        $address = new Address($id_address_delivery);
        $id_country = $address->id_country;
        if ($id_country == 0) {
            $id_country = $context->country->id;
        }
        $id_state = $address->id_state;
        $configsPricesDrop = $groupinc->getPricesDropConfigurations($id_shop, $id_customer, $id_country, $id_state, $id_currency, $id_lang, 0);
        if (!empty($configsPricesDrop)) {
            foreach ($configsPricesDrop as $conf) {
                $products = $groupinc->getProductsPricesDrop($conf, $id_shop, $id_lang);
                if (!empty($products)) {
                    foreach ($products as $p) {
                        array_push($tab_id_product, (int)$p);
                    }
                }
            }
        }
        $front = true;
        if (!in_array($context->controller->controller_type, array('front', 'modulefront'))) {
            $front = false;
        }
        $sql_groups = '';
        if (Group::isFeatureActive()) {
            $groups = FrontController::getCurrentCustomerGroups();
            $sql_groups = ' AND EXISTS(SELECT 1 FROM `'._DB_PREFIX_.'category_product` cp
                JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')
                WHERE cp.`id_product` = p.`id_product`)';
        }
        if ($count) {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT COUNT(DISTINCT p.`id_product`)
            FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p').'
            WHERE product_shop.`active` = 1
            AND product_shop.`show_price` = 1
            '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
            '.((!$beginning && !$ending) ? 'AND p.`id_product` IN('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
            '.$sql_groups);
        }
        if (strpos($order_by, '.') > 0) {
            $order_by = explode('.', $order_by);
            $order_by = pSQL($order_by[0]).'.`'.pSQL($order_by[1]).'`';
        }
        $sql = '
                SELECT
            p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, pl.`description`, pl.`description_short`, pl.`available_now`, pl.`available_later`,
            IFNULL(product_attribute_shop.id_product_attribute, 0) id_product_attribute,
            pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`,
            pl.`name`, image_shop.`id_image` id_image, il.`legend`, m.`name` AS manufacturer_name,
            DATEDIFF(
                p.`date_add`,
                DATE_SUB(
                    "'.date('Y-m-d').' 00:00:00",
                    INTERVAL '.(Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY
                )
            ) > 0 AS new
        FROM `'._DB_PREFIX_.'product` p
        '.Shop::addSqlAssociation('product', 'p').'
        LEFT JOIN `'._DB_PREFIX_.'product_attribute_shop` product_attribute_shop
            ON (p.`id_product` = product_attribute_shop.`id_product` AND product_attribute_shop.`default_on` = 1 AND product_attribute_shop.id_shop='.(int)$context->shop->id.')
        '.Product::sqlStock('p', 0, false, $context->shop).'
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
            p.`id_product` = pl.`id_product`
            AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
        )
        LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop
            ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop='.(int)$context->shop->id.')
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        WHERE product_shop.`active` = 1
        AND product_shop.`show_price` = 1
        '.($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '').'
        '.((!$beginning && !$ending) ? ' AND p.`id_product` IN ('.((is_array($tab_id_product) && count($tab_id_product)) ? implode(', ', $tab_id_product) : 0).')' : '').'
        '.$sql_groups.'
        ORDER BY '.(isset($order_by_prefix) ? pSQL($order_by_prefix).'.' : '').pSQL($order_by).' '.pSQL($order_way).'
        LIMIT '.(int)(($page_number-1) * $nb_products).', '.(int)$nb_products;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$result) {
            return false;
        }
        if ($order_by == 'price') {
            Tools::orderbyPrice($result, $order_way);
        }
        return Product::getProductsProperties($id_lang, $result);
    }

    public static function getProductProperties($id_lang, $row, Context $context = null)
    {
        if (!Module::isEnabled('groupinc') || (empty($row) || !isset($row) || !isset($row['id_product']))) {
            return parent::getProductProperties($id_lang, $row, $context);
        }
        $context = Context::getContext();
        if (isset($context->controller) && in_array($context->controller->controller_type, array('admin'))) {
            return parent::getProductProperties($id_lang, $row, $context);
        }
        $row = parent::getProductProperties($id_lang, $row, $context);
        if (Module::isEnabled('groupinc')) {
            include_once(_PS_MODULE_DIR_.'groupinc/classes/GroupincConfiguration.php');
            $row = GroupincConfiguration::getProductProperties($id_lang, $row, $context);
        }
        return $row;
    }
}
}