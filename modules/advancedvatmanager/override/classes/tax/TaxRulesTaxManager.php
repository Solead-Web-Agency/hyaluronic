<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class TaxRulesTaxManager extends TaxRulesTaxManagerCore
{
    public function getTaxCalculator()
    {
        if (Module::isEnabled('advancedvatmanager')) {
            static $tax_enabled = null;
            if (isset($this->tax_calculator)) {
                return $this->tax_calculator;
            }
            if ($tax_enabled === null) {
                $tax_enabled = Configuration::get('PS_TAX');
            }
            if (!$tax_enabled) {
                return new TaxCalculator([]);
            }
            $taxes = [];
            $postcode = 0;
            if (!empty($this->address->postcode)) {
                $postcode = $this->address->postcode;
            }
            $cache_id = (int)$this->address->id_country.'-'.
                (int)$this->address->id_state.'-'.$postcode.'-'.(int)$this->type;
            if (!Cache::isStored($cache_id)) {
                $rows = Db::getInstance()->executeS('
                    SELECT tr.*
                    FROM `' . _DB_PREFIX_ . 'tax_rule` tr
                    JOIN `' . _DB_PREFIX_ . 'tax_rules_group` trg ON (tr.`id_tax_rules_group` = trg.`id_tax_rules_group`)
                    WHERE trg.`active` = 1
                    AND tr.`id_country` = ' . (int) $this->address->id_country . '
                    AND tr.`id_tax_rules_group` = ' . (int) $this->type . '
                    AND tr.`id_state` IN (0, ' . (int) $this->address->id_state . ')
                    AND (\'' . pSQL($postcode) . '\' BETWEEN tr.`zipcode_from` AND tr.`zipcode_to`
                        OR (tr.`zipcode_to` = 0 AND tr.`zipcode_from` IN(0, \'' . pSQL($postcode) . '\')))
                    ORDER BY tr.`zipcode_from` DESC, tr.`zipcode_to` DESC, tr.`id_state` DESC, tr.`id_country` DESC');
                $behavior = 0;
                $first_row = true;
                
                require_once(_PS_MODULE_DIR_.'advancedvatmanager/classes/ValidationEngine.php');
                $noTax = ValidationEngine::$notax_customer;
                
                if (!$noTax) {
                    foreach ($rows as $row) {
                        $tax = new Tax((int) $row['id_tax']);
                            $taxes[] = $tax;
                        if ($first_row) {
                            $behavior = $row['behavior'];
                            $first_row = false;
                        }
                        if ($row['behavior'] == 0) {
                            break;
                        }
                    }
                }
                $result = new TaxCalculator($taxes, $behavior);
                Cache::store($cache_id, $result);
                return $result;
            }
            return Cache::retrieve($cache_id);
        }
        else {
            return parent::getTaxCalculator();
        }
    }
}
