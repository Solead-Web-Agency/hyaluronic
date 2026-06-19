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
 * @author    SeoSA <885588@bk.ru>
 * @copyright 2012-2019 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class AccessoriesTabMEP extends BaseTabMEP
{
    public $accessories;
    public $remove_old;

    public function __construct()
    {
        parent::__construct();
        $this->accessories = Tools::getValue('accessories');
        $this->remove_old = (int)Tools::getValue('remove_old');
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        foreach ($products as $product) {
            $product_obj = new Product((int)$product);
            if (Validate::isLoadedObject($product_obj)) {
                $this->addToReIndexSearch((int)$product);
                if ($this->remove_old) {
                    $product_obj->deleteAccessories();
                    if (!is_array($this->accessories) || !count($this->accessories)) {
                        continue;
                    }
                }

                $products_accessories = Product::getAccessoriesLight(
                    $this->context->language->id,
                    $product_obj->id
                );
                $ids_products_accessories = array();
                foreach ($products_accessories as $products_accessory) {
                    $ids_products_accessories[] = (int)$products_accessory['id_product'];
                }

                foreach ($this->accessories as $accessory) {
                    if (!in_array((int)$accessory['id'], $ids_products_accessories)) {
                        Db::getInstance()->execute(
                            'INSERT INTO `'._DB_PREFIX_.'accessory` (`id_product_1`, `id_product_2`)
							VALUES ('.(int)$product_obj->id.', '.(int)$accessory['id'].')'
                        );
                    }
                }
            }
        }
        $return_products = array();

        return array(
            'products' => $return_products,
        );
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkBeforeChange()
    {
        if ((!is_array($this->accessories) || !count($this->accessories)) && !$this->remove_old) {
            LoggerMEP::getInstance()->error($this->l('No accessories'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Accessories');
    }
}
