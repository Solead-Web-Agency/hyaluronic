<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class SortMEP
{
    public function reindexPositionsByPrice()
    {
        $categories = Category::getSimpleCategories($this->context->language->id);
        foreach ($categories as $category) {
            $id_category = $category['id_category'];
            $products = $this->getProductsCategory($id_category);
            usort($products, array($this, 'sortProductByPrice'));
            foreach ($products as $key => $product) {
                Db::getInstance()->update(
                    'category_product',
                    array(
                        'position' => $key
                    ),
                    ' id_product = '.(int)$product['id_product'].' AND id_category = '.(int)$id_category
                );
            }
        }
    }

    public function getProductsCategory($id_category)
    {
        return Db::getInstance()->executeS(
            'SELECT id_product FROM '._DB_PREFIX_.'category_product
            WHERE id_category = '.(int)$id_category
        );
    }

    public function sortProductByPrice($a, $b)
    {
        $price_a = Product::getPriceStatic($a['id_product']);
        $price_b = Product::getPriceStatic($b['id_product']);
        if ($price_a == $price_b) {
            return 0;
        }
        return ($price_a < $price_b ? -1 : 1);
    }
}
