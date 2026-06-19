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

class ManufacturerTabMEP extends BaseTabMEP
{
    public $id_manufacturer;

    public $manufacturer;

    public function __construct()
    {
        parent::__construct();
        $this->id_manufacturer = (int)Tools::getValue('id_manufacturer');
        if ($this->id_manufacturer === 0) {
            $this->manufacturer = new Manufacturer();
            $this->manufacturer->name = '';
        } else {
            $this->manufacturer = new Manufacturer(
                $this->id_manufacturer,
                $this->context->language->id
            );
        }
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        Db::getInstance()->update(
            'product',
            array(
                'id_manufacturer' => (int)$this->manufacturer->id,
            ),
            ' id_product IN(' . pSQL(implode(',', $products)) . ')'
        );
        $return_products = array();
        foreach ($products as $product) {
            $return_products[(int)$product] = $this->manufacturer->name;
        }

        return array(
            'products' => $return_products,
        );
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function checkBeforeChange()
    {
        if (!Validate::isLoadedObject($this->manufacturer)) {
            LoggerMEP::getInstance()->error($this->l('Manufacturer not exists'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Manufacturer');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['manufacturers'] = Manufacturer::getManufacturers(
            false,
            0,
            true,
            false,
            false,
            false,
            true
        );
        return $variables;
    }
}
