<?php
/**
 * 2007-2018 PrestaShop
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
 * @copyright 2012-2020 SeoSA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class ActiveTabMEP extends BaseTabMEP
{
    public function applyChangeBoth($products, $combinations)
    {
        $delete_product = (int)Tools::getValue('delete_product', 0);
        $delete_type = (int)Tools::getValue('delete_type', 0);

        $delete_combinations = array();
        if ($this->checkAccessField('delete_product') && $delete_product && $delete_type) {
            foreach ($combinations as $id_pa) {
                $combination = new Combination($id_pa);
                if (Validate::isLoadedObject($combination)) {
                    $this->addToReIndexSearch((int)$combination->id_product);
                    $combination->delete();
                    $delete_combinations[] = $id_pa;
                }
            }
        }
        $this->result['delete_combinations'] = $delete_combinations;
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function applyChangeForProducts($products)
    {
        $active = (int)Tools::getValue('active');
        $delete_product = (int)Tools::getValue('delete_product', 0);
        $delete_type = (int)Tools::getValue('delete_type', 0);

        $visibility = Tools::getValue('visibility');
        $condition = Tools::getValue('condition');
        $available_for_order = (int)Tools::getValue('available_for_order');
        $show_price = ($available_for_order ? 1 : (int)Tools::getValue('show_price'));
        $online_only = (int)Tools::getValue('online_only');
        $on_sale = (int)Tools::getValue('on_sale');
        if (_PS_VERSION_ >= 1.7) {
            $show_condition = (int)Tools::getValue('show_condition');
        }
        $return_products = array();
        $delete_products = array();
        foreach ($products as $product) {
            $data_for_update = array();

            if ($this->checkAccessField('on_sale')) {
                $this->addToReIndexSearch((int)$product);
                MassEditTools::updateObjectField(
                    'Product',
                    'on_sale',
                    (int)$product,
                    $on_sale
                );
            }
            if (_PS_VERSION_ >= 1.7) {
                if ($this->checkAccessField('show_condition')) {
                    $this->addToReIndexSearch((int)$product);
                    MassEditTools::updateObjectField(
                        'Product',
                        'show_condition',
                        (int)$product,
                        $show_condition
                    );
                }
            }
            if ($this->checkAccessField('active') && $active != -1) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['active'] = (int)$active;
            }

            if ($this->checkAccessField('visibility') && $visibility != -1) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['visibility'] = pSQL($visibility);
            }

            if ($this->checkAccessField('condition') && $condition != -1) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['condition'] = pSQL($condition);
            }

            if ($this->checkAccessField('available_for_order')) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['available_for_order'] = (int)$available_for_order;
            }

            if ($this->checkAccessField('show_price')) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['show_price'] = (int)$show_price;
            }

            if ($this->checkAccessField('online_only')) {
                $this->addToReIndexSearch((int)$product);
                $data_for_update['online_only'] = (int)$online_only;
            }

            if ($this->checkAccessField('delete_product') && $delete_product && !$delete_type) {
                $this->addToReIndexSearch((int)$product);
                $product_obj = new Product($product);
                if (Validate::isLoadedObject($product_obj)) {
                    $product_obj->delete();
                    $delete_products[] = $product;
                }
            }

            if (!Shop::isFeatureActive()) {
                Db::getInstance()->update('product', $data_for_update, ' id_product = '.(int)$product);
            }
            Db::getInstance()->update(
                'product_shop',
                $data_for_update,
                ' id_product = '.(int)$product.' '
                .(Shop::isFeatureActive() && $this->sql_shop ? ' AND id_shop '.$this->sql_shop : '')
            );
            $return_products[(int)$product] = $active;
        }

        return array(
            'products' => $return_products,
            'delete_products' => $delete_products,
        );
    }

    public function getTitle()
    {
        return $this->l('Active');
    }
}
