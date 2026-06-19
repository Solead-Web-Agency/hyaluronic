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

class ReferenceTabMEP extends BaseTabMEP
{
    public $assignment;
    public $reference;

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product` '
            . 'SET ' . implode(', ', $this->assignment)
            . ' WHERE `id_product` IN (\'' . implode('\', \'', $products) . '\')';

        if (!Db::getInstance()->execute($sql)) {
            LoggerMEP::getInstance()->error($this->l('Error writing to database'));
            return array();
        }

        return array(
            'ids_product' => $products,
            'reference' => $this->reference
        );
    }

    public function applyChangeForCombinations($products)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'product_attribute` '
            . 'SET ' . implode(', ', $this->assignment)
            . ' WHERE `id_product_attribute` 
            IN (\'' . implode('\', \'', $this->getCombinationsIdsFromRequest()) . '\')';

        if (!Db::getInstance()->execute($sql)) {
            LoggerMEP::getInstance()->error($this->l('Error writing to database'));
            return array();
        }

        return array(
            'ids_product' => array_keys($products),
            'reference' => $this->reference
        );
    }

    public function checkOptionForCombination()
    {
        $change_for_property = (int)Tools::getValue('change_for_property');
        if ($change_for_property == self::CHANGE_FOR_COMBINATION) {
            return true;
        }
        return false;
    }

    public function checkBeforeChange()
    {
        if ($this->checkAccessField('selected_reference')) {
            $this->reference = Tools::getValue('reference');
            $this->assignment[] = '`reference` = \'' . pSQL($this->reference) . '\'';
        }

        if ($this->checkAccessField('selected_ean13')) {
            $this->assignment[] = '`ean13` = \'' . pSQL(Tools::getValue('ean13')) . '\'';
        }

        if ($this->checkAccessField('selected_upc')) {
            $this->assignment[] = '`upc` = \'' . pSQL(Tools::getValue('upc')) . '\'';
        }

        if (!count($this->assignment)) {
            LoggerMEP::getInstance()->error($this->l('Not selected field'));
            return array();
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Reference');
    }
}
