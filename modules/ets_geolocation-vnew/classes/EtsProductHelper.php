<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 **/
if (!class_exists('\EtsProductHelper')) {
    /**
     * Class EtsProductHelper
     */
    class EtsProductHelper
    {
        /**
         * @var \EtsProductHelper
         */
        private static $_instance;

        /**
         * @return \EtsProductHelper
         */
        public static function getInstance()
        {
            if (!self::$_instance instanceof self) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Search products by name or REF or id
         *
         * @param string $query
         * @param bool $echoResult pass TRUE to echo result directly (for jQuery autocomplete)
         * @param int $limit Limit the result, pass FALSE to disable limit
         * @param int $offset
         *
         * @return array
         *
         * @throws \PrestaShopDatabaseException
         */
        public function searchProducts($query, $echoResult = false, $limit = 10, $offset = 0)
        {
            $context = Context::getContext();
            $query = trim(strip_tags($query));
            if ($query == '') {
                if (!$echoResult) {
                    return [];
                }
                exit;
            }
            $is17 = false;
            if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                $is17 = true;
            }
            // search product.
            $imageType = $is17 ? ImageType::getFormattedName('cart') : ImageType::getFormatedName('cart');
            $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
        		FROM `' . _DB_PREFIX_ . 'product` p
        		' . Shop::addSqlAssociation('product', 'p') . '
        		LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $context->language->id . Shop::addSqlRestrictionOnLang('pl') . ')
        		LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
        			ON (image_shop.`id_product` = p.`id_product` AND image_shop.id_shop=' . (int) $context->shop->id . ')
        		LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $context->language->id . ')
        		WHERE  (pl.id_product LIKE \'%' . pSQL($query) . '%\' OR pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\' ' . (Validate::isInt($query) && (int) $query > 0 ? ' OR p.id_product=' . (int) $query : '') . ') GROUP BY p.id_product';
            if ($limit && Validate::isUnsignedInt($limit)) {
                $sql .= sprintf(' LIMIT %d', $limit);
                if ($offset && Validate::isInt($offset)) {
                    $sql .= sprintf(' OFFSET %d', $offset);
                }
            }
            $items = Db::getInstance()->executeS($sql);
            if (is_array($items) && $items) {
                $results = [];
                foreach ($items as $item) {
                    $results[] = [
                        'id_product' => (int) $item['id_product'],
                        'id_product_attribute' => 0,
                        'name' => $item['name'],
                        'attribute' => '',
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], $imageType)),
                    ];
                }
                if ($echoResult) {
                    foreach ($results as $item) {
                        echo trim($item['id_product'] . '|' . (int) $item['id_product_attribute'] . '|' . Tools::ucfirst($item['name']) . '|' . $item['attribute'] . '|' . $item['ref'] . '|' . $item['image']) . "\n";
                    }
                    exit;
                }

                return $results;
            }
            if ($echoResult) {
                exit;
            }

            return [];
        }
    }
}
