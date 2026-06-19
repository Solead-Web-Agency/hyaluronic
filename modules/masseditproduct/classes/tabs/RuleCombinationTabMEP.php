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

class RuleCombinationTabMEP extends BaseTabMEP
{
    public $exact_match;
    public $delete_attribute;
    public $add_attribute;
    public $force_delete_attribute;
    public $selected_attributes;
    public $rc_apply_change_for;

    public function __construct()
    {
        parent::__construct();
        $this->exact_match = (int)Tools::getValue('exact_match');
        $this->rc_apply_change_for = (int)Tools::getValue('rc_apply_change_for');
        $this->delete_attribute = (int)Tools::getValue('delete_attribute');
        $this->add_attribute = (int)Tools::getValue('add_attribute');
        $this->force_delete_attribute = (int)Tools::getValue('force_delete_attribute');
        $this->selected_attributes = (is_array(Tools::getValue('selected_attributes')) ?
            array_map('intval', Tools::getValue('selected_attributes'))
            : array());
    }

    public function checkOptionForCombination()
    {
        return $this->rc_apply_change_for;
    }

    public function applyChangeBoth($products, $combinations)
    {
        $change_combinations = array();
        if ($this->rc_apply_change_for == 1) {
             $change_combinations = $combinations;
        }

        if ($this->checkAccessField('selected_attributes')) {
            $ids_product_attributes = array();
            if (count($change_combinations)) {
                $ids_product_attribute = $change_combinations;
            }

            foreach ($this->selected_attributes as $selected_attribute) {
                if (version_compare(_PS_VERSION_, '1.6', '<')) {
                    $result = Db::getInstance()->executeS(
                        'SELECT pac.`id_product_attribute`, p.`id_product`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
				 ON pas.`id_product_attribute` = pac.`id_product_attribute`
				 LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
				 ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p
				 ON p.`id_product` = pa.`id_product`
				WHERE pac.`id_attribute` = ' . (int)$selected_attribute . ' AND pas.`id_shop` 
				IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                        . (count($ids_product_attributes) ?
                            ' AND pac.`id_product_attribute` IN(' . implode(
                                ',',
                                array_map('intval', $ids_product_attributes)
                            ) . ') ' : '')
                    );
                } else {
                    $result = Db::getInstance()->executeS(
                        'SELECT pac.`id_product_attribute`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas 
				ON pas.`id_product_attribute` = pac.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.`id_product` = pas.`id_product`
				WHERE pac.`id_attribute` = ' . (int)$selected_attribute . ' AND pas.`id_shop` 
				IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                        . (count($ids_product_attributes) ?
                            ' AND pac.`id_product_attribute` IN(' . implode(
                                ',',
                                array_map('intval', $ids_product_attributes)
                            ) . ') ' : '')
                    );
                }
                if (is_array($result)) {
                    $ids_product_attributes = array();

                    foreach ($result as $item) {
                        $ids_product_attributes[] = (int)$item['id_product_attribute'];
                    }
                    $ids_product_attributes = array_unique($ids_product_attributes);
                }
            }

            if (count($ids_product_attributes)) {
                foreach ($ids_product_attributes as $ids_product_attribute) {
                    $combination = new Combination($ids_product_attribute);
                    $attributes = $combination->getAttributesName($this->context->language->id);
                    if (count($this->selected_attributes) == count($attributes) || !$this->exact_match) {
                        $combination->delete();
                    }
                }
            }
        }

        if ($this->checkAccessField('delete_attribute')) {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $result = Db::getInstance()->executeS(
                    'SELECT pac.`id_product_attribute`, p.`id_product`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac 
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas 
				ON pas.`id_product_attribute` = pac.`id_product_attribute`
				 LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa 
				 ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.`id_product` = pa.`id_product`
				WHERE pac.`id_attribute` = ' . (int)$this->delete_attribute . '
				 AND pas.`id_shop` IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                    . (count($change_combinations) ? ' AND pas.`id_product_attribute` IN(' . implode(
                        ',',
                        array_map('intval', $change_combinations)
                    ) . ')' : '')
                );
            } else {
                $result = Db::getInstance()->executeS(
                    'SELECT pac.`id_product_attribute`, p.`id_product`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas 
				ON pas.`id_product_attribute` = pac.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p ON p.`id_product` = pas.`id_product`
				WHERE pac.`id_attribute` = ' . (int)$this->delete_attribute . ' 
				AND pas.`id_shop` IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                    . (count($change_combinations) ? ' AND pas.`id_product_attribute` IN(' . implode(
                        ',',
                        array_map('intval', $change_combinations)
                    ) . ')' : '')
                );
            }
            $tmp_products = $products;

            $product_attributes = array();
            if (is_array($result)) {
                foreach ($result as $item) {
                    $key = array_search($item['id_product'], $tmp_products);
                    if ($key !== false) {
                        unset($tmp_products[$key]);
                    }

                    $product_attributes[(int)$item['id_product_attribute']] = $item;
                }
            }

            if (count($product_attributes)) {
                foreach ($product_attributes as $product_attribute) {
                    if ($this->force_delete_attribute
                        || !($choice_pa = MassEditTools::checkProductOnChoiceAttributes(
                            $product_attribute['id_product'],
                            $product_attribute['id_product_attribute'],
                            $this->delete_attribute
                        ))) {
                        Db::getInstance()->delete(
                            'product_attribute_combination',
                            ' `id_attribute` = ' . (int)$this->delete_attribute
                            . ' AND `id_product_attribute` = ' . (int)$product_attribute['id_product_attribute']
                        );
                    } else {
                        LoggerMEP::getInstance()->error(
                            sprintf(
                                $this->l('For product with ids %s found choice combination with id %s'),
                                $product_attribute['id_product'],
                                $choice_pa
                            )
                        );
                    }
                }
            }

            if (count($tmp_products)) {
                LoggerMEP::getInstance()->error(
                    sprintf(
                        $this->l('No attribute applied to products with id %s'),
                        implode(', ', $tmp_products)
                    )
                );
            }
        }

        if ($this->checkAccessField('add_attribute')) {
            $change_combinations = $combinations;
            if (empty($change_combinations)) {
                LoggerMEP::getInstance()->error(
                    sprintf(
                        $this->l('No attribute applied to products with id %s'),
                        implode(', ', $tmp_products)
                    )
                );
                return false;
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $result = Db::getInstance()->executeS(
                    'SELECT pac.`id_product_attribute`, p.`id_product`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
				 ON pas.`id_product_attribute` = pac.`id_product_attribute`
				 LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
				 ON pac.`id_product_attribute` = pa.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p
				 ON p.`id_product` = pa.`id_product`
				WHERE pac.`id_attribute`
				 AND pas.`id_shop` IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                    . (count($change_combinations) ? ' AND pas.`id_product_attribute` IN(' . implode(
                        ',',
                        array_map('intval', $change_combinations)
                    ) . ')' : '')
                );
            } else {
                $result = Db::getInstance()->executeS(
                    'SELECT pac.`id_product_attribute`, p.`id_product`
				FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
				LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
				 ON pas.`id_product_attribute` = pac.`id_product_attribute`
				LEFT JOIN ' . _DB_PREFIX_ . 'product p
				 ON p.`id_product` = pas.`id_product`
				WHERE pac.`id_attribute`
				 AND pas.`id_shop` IN(' . implode(',', array_map('intval', $this->ids_shop)) . ')
				AND p.`id_product` IN(' . implode(',', array_map('intval', $products)) . ')'
                    . (count($change_combinations) ? ' AND pas.`id_product_attribute` IN(' . implode(
                        ',',
                        array_map('intval', $change_combinations)
                    ) . ')' : '')
                );
            }
            $tmp_products = $products;

            $product_attributes = array();
            if (is_array($result)) {
                foreach ($result as $item) {
                    $key = array_search($item['id_product'], $tmp_products);
                    if ($key !== false) {
                        unset($tmp_products[$key]);
                    }

                    $product_attributes[(int)$item['id_product_attribute']] = $item;
                }
            }

            if (count($product_attributes)) {
                foreach ($product_attributes as $product_attribute) {
                    if (!MassEditTools::checkProductOnChoiceAttributesReverse(
                        $product_attribute['id_product'],
                        $product_attribute['id_product_attribute'],
                        $this->add_attribute
                    )) {
                        Db::getInstance()->insert(
                            'product_attribute_combination',
                            array(
                                'id_attribute' => (int)$this->add_attribute,
                                'id_product_attribute' => (int)$product_attribute['id_product_attribute'],
                            )
                        );
                    } else {
                        LoggerMEP::getInstance()->error(
                            sprintf(
                                $this->l('Product with id %s combination id %s has this attribute'),
                                $product_attribute['id_product'],
                                $product_attribute['id_product_attribute']
                            )
                        );
                    }
                }
            }

            if (count($tmp_products)) {
                LoggerMEP::getInstance()->error(
                    sprintf(
                        $this->l('No attribute applied to products with id %s'),
                        implode(', ', $tmp_products)
                    )
                );
            }
        }
        return array();
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function applyChangeForProducts($products)
    {
    }

    public function checkBeforeChange()
    {
        if ($this->checkAccessField('selected_attributes') && !count($this->selected_attributes)) {
            LoggerMEP::getInstance()->error($this->l('No selected attributes'));
        }
        if (LoggerMEP::getInstance()->hasError()) {
            return false;
        }
        return true;
    }

    public function getTitle()
    {
        return $this->l('Combinations');
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $attribute_groups = AttributeGroup::getAttributesGroups($this->context->language->id);
        if (is_array($attribute_groups) && count($attribute_groups)) {
            foreach ($attribute_groups as &$attribute_group) {
                $attribute_group['attributes'] = AttributeGroup::getAttributes(
                    $this->context->language->id,
                    (int)$attribute_group['id_attribute_group']
                );
            }
        }
        $variables['attribute_groups'] = $attribute_groups;
        return $variables;
    }
}
