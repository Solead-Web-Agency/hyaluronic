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

class DeliveryTabMEP extends BaseTabMEP
{
    public $width;
    public $height;
    public $depth;
    public $weight;
    public $additional_shipping_cost;
    public $carriers;
    public $weight_change_for_combination;
    public $additional_delivery_times;
    public $delivery_in_stock = array();
    public $delivery_out_stock;
    public $del_carrier;

    public function __construct()
    {
        parent::__construct();
        $this->width = (float)Tools::getValue('width');
        $this->height = (float)Tools::getValue('height');
        $this->depth = (float)Tools::getValue('depth');
        $this->weight = (float)Tools::getValue('weight');
        $this->additional_shipping_cost = (float)Tools::getValue('additional_shipping_cost');
        $this->carriers = array_map('intval', Tools::getValue('id_carrier', array()));
        $this->weight_change_for_combination = Tools::getValue('change_for');
        $this->additional_delivery_times = Tools::getValue('additional_delivery_times');
        $this->del_carrier = (int)Tools::getValue('del_carrier');
    }

    public function applyChangeBoth($products, $combinations)
    {
        foreach ($products as $id_product) {
            $product = new Product((int)$id_product);
            if (Validate::isLoadedObject($product)) {
                if ($this->checkAccessField('width')) {
                    $product->width = $this->width;
                }

                if ($this->checkAccessField('height')) {
                    $product->height = $this->height;
                }

                if ($this->checkAccessField('depth')) {
                    $product->depth = $this->depth;
                }

                if ($this->checkAccessField('weight')) {
                    if ($this->weight_change_for_combination == 1) {
                        foreach ($combinations as $id_pa) {
                            MassEditTools::updateObjectField(
                                'Combination',
                                'weight',
                                (int)$id_pa,
                                $this->weight
                            );
                        }
                    } else {
                        $product->weight = $this->weight;
                    }
                }

                if ($this->checkAccessField('additional_shipping_cost')) {
                    $product->additional_shipping_cost = $this->additional_shipping_cost;
                }

                /** 1.7 */
                if ($this->checkAccessField('additional_delivery_times')) {
                    $product->additional_delivery_times = $this->additional_delivery_times;
                }

                if ($this->checkAccessField('delivery_in_stock')) {
                    foreach (ToolsModuleMEP::getLanguages(false) as $language) {
                        $this->delivery_in_stock[$language['id_lang']]
                            = Tools::getValue('delivery_in_stock_'.$language['id_lang']);
                    }
                    $product->delivery_in_stock = $this->delivery_in_stock;
                }

                if ($this->checkAccessField('delivery_out_stock')) {
                    foreach (ToolsModuleMEP::getLanguages(false) as $language) {
                        $this->delivery_out_stock[$language['id_lang']]
                            = Tools::getValue('delivery_out_stock_'.$language['id_lang']);
                    }
                    $product->delivery_out_stock = $this->delivery_out_stock;
                }

                $product->save();

                if (is_array($this->carriers) && count($this->carriers)
                    && $this->checkAccessField('id_carrier') &&  $this->del_carrier == 0) {
                    $product->setCarriers($this->carriers);
                } elseif ($this->checkAccessField('id_carrier')) {
                    $res = Db::getInstance()->executeS('SELECT id_reference 
                               FROM `'._DB_PREFIX_.'carrier` 
                               WHERE id_carrier IN ('.pSQL(implode(',', $this->carriers)).')');
                    if (empty($res)) {
                        return;
                    }
                    $referrences = array();
                    foreach ($res as $ref) {
                        $referrences[] = $ref['id_reference'];
                    }
                    Db::getInstance()->execute(
                        'DELETE FROM `'._DB_PREFIX_.'product_carrier`
			WHERE id_product = '.(int)$product->id.'
			AND id_shop = '.(int)Context::getContext()->shop->id.' AND id_carrier_reference 
			IN ('.pSQL(implode(',', $referrences)).')'
                    );
                }
            }
        }
    }

    public function applyChangeForProducts($products)
    {
        return array();
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkBeforeChange()
    {
        $combinations = $this->getCombinationsIdsFromRequest();
        if ($this->checkAccessField('weight') && $this->weight_change_for_combination
            && (!is_array($combinations) || !count($combinations))) {
            LoggerMEP::getInstance()->error($this->l('No combinations for change weight'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Delivery');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['carriers'] = Carrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        return $variables;
    }
    
    public function checkOptionForCombination()
    {
        $change_for = (int)Tools::getValue('change_for');
        return $change_for == self::CHANGE_FOR_COMBINATION;
    }
}
