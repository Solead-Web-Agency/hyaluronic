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

class AdvancedStockManagementTabMEP extends BaseTabMEP
{
    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        $advanced_stock_management = Tools::getValue('advanced_stock_management', false);
        $depends_on_stock = Tools::getValue('depends_on_stock', false);

        $return_products = array();

        foreach ($products as $id_product) {
            if ($advanced_stock_management === false) {
                LoggerMEP::getInstance()->error($this->l('Undefined value'));
            }
            if ((int)$advanced_stock_management != 1 && (int)$advanced_stock_management != 0) {
                LoggerMEP::getInstance()->error($this->l('Incorrect value'));
            }
            if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int)$advanced_stock_management == 1) {
                LoggerMEP::getInstance()->error($this->l('Not possible if advanced stock management is disabled. '));
            }
            $product_obj = new Product((int)$id_product);
            $product_obj->setAdvancedStockManagement((int)$advanced_stock_management);
            if (StockAvailable::dependsOnStock($product_obj->id) == 1 && (int)$advanced_stock_management == 0) {
                StockAvailable::setProductDependsOnStock($product_obj->id, 0);
            }

            if ($depends_on_stock === false) {
                {
                    LoggerMEP::getInstance()->error($this->l('Undefined value'));
                }
            }
            if ((int)$depends_on_stock != 0 && (int)$depends_on_stock != 1) {
                LoggerMEP::getInstance()->error($this->l('Incorrect value'));
            }
            if (!$product_obj->advanced_stock_management && (int)$depends_on_stock == 1) {
                LoggerMEP::getInstance()->error($this->l('Not possible if advanced stock management is disabled. '));
            }
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
                && (int)$depends_on_stock == 1
                && (Pack::isPack($product_obj->id)
                    && !Pack::allUsesAdvancedStockManagement($product_obj->id)
                    && ($product_obj->pack_stock_type == 2 || $product_obj->pack_stock_type == 1 ||
                        ($product_obj->pack_stock_type == 3
                            && (Configuration::get('PS_PACK_STOCK_TYPE') == 1
                                || Configuration::get('PS_PACK_STOCK_TYPE') == 2))))) {
                LoggerMEP::getInstance()->error(
                    $this->l(
                        'You cannot use advanced stock management for this 
                      pack because<br />
                      - advanced stock management is not enabled for these products<br />
                      - you have chosen to decrement products quantities.'
                    )
                );
            }

            StockAvailable::setProductDependsOnStock($product_obj->id, (int)$depends_on_stock);

            $return_products[(int)$id_product] = (bool)$advanced_stock_management;
        }

        return array(
            'products' => $return_products,
        );
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkAvailable()
    {
        return (int)Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
    }

    public function getTitle()
    {
        return $this->l('Stock management');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['product'] = new Product();
        return $variables;
    }
}
