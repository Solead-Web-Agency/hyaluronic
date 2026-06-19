<?php
/**
 * 2007-2025 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2025 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AmazonPayRestrictedProductsHelper
{
    /**
     * @param $id_product
     *
     * @return bool
     */
    public static function isRestrictedProduct($id_product)
    {
        $raw = trim((string) Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'));
        if ($raw === '') {
            return false;
        }
        $restrictedCategories = array_filter(
            array_map('trim', explode(',', $raw)),
            static function ($v) {
                return $v !== '';
            }
        );
        if (!$restrictedCategories) {
            return false;
        }
        $restrictedCategories = self::prepRestrictedCategories($restrictedCategories);
        if (Product::idIsOnCategoryId((int) $id_product, $restrictedCategories)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function cartHasRestrictedProducts()
    {
        $raw = trim((string) Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'));
        if ($raw === '') {
            return false;
        }
        $restrictedCategories = array_filter(
            array_map('trim', explode(',', $raw)),
            static function ($v) {
                return $v !== '';
            }
        );
        if (!$restrictedCategories) {
            return false;
        }
        $restrictedCategories = self::prepRestrictedCategories($restrictedCategories);
        $context = Context::getContext();
        if (!$context->cart || !$context->cart->id) {
            return false;
        }
        $cart = $context->cart;
        $productsCart = $cart->getProducts();
        foreach ($productsCart as $productCart) {
            if (Product::idIsOnCategoryId((int) $productCart['id_product'], $restrictedCategories)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $restrictedCategories
     *
     * @return array
     */
    protected static function prepRestrictedCategories($restrictedCategories)
    {
        $tmp = [];
        foreach ($restrictedCategories as $restrictedCategory) {
            $tmp[] = ['id_category' => $restrictedCategory];
        }

        return $tmp;
    }
}
