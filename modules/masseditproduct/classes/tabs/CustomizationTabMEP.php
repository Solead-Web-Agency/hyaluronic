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

class CustomizationTabMEP extends BaseTabMEP
{
    public function applyChangeForCombinations($products)
    {
    }

    public function applyChangeForProducts($products)
    {
        $labels = Tools::getValue('label');
        if (!is_array($labels) || !count($labels)) {
            LoggerMEP::getInstance()->error($this->l('Not labels'));
            return array();
        }

        $file_fields = (array_key_exists(Product::CUSTOMIZE_FILE, $labels)
        && is_array($labels[Product::CUSTOMIZE_FILE]) ? $labels[Product::CUSTOMIZE_FILE] : array());

        $text_fields = (array_key_exists(Product::CUSTOMIZE_TEXTFIELD, $labels)
        && is_array($labels[Product::CUSTOMIZE_TEXTFIELD]) ? $labels[Product::CUSTOMIZE_TEXTFIELD] : array());

        if ($this->checkAccessField('delete_customization_fields') && Tools::getValue('delete_customization_fields')) {
            Db::getInstance()->execute(
                'DELETE cf, cfl 
            FROM `'._DB_PREFIX_.'customization_field` cf
            LEFT JOIN `'._DB_PREFIX_.'customization_field_lang` cfl
            ON cf.`id_customization_field` = cfl.`id_customization_field`
            WHERE cf.`id_product` IN('.implode(',', array_map('intval', $products)).')'
            );

            Db::getInstance()->execute(
                'UPDATE '._DB_PREFIX_.'product p, '._DB_PREFIX_.'product_shop ps
                SET p.`uploadable_files` = 0,
                    p.`text_fields` = 0,
                    ps.`uploadable_files` = 0,
                    ps.`text_fields` = 0
                WHERE p.`id_product` = ps.`id_product`
                AND p.`id_product` IN('.implode(',', array_map('intval', $products)).')'
            );
        }

        $fields = array(
            'uploadable_files' => 0,
            'text_fields' => 0,
        );

        if ($this->checkAccessField('customization_file_labels')) {
            $fields['uploadable_files'] = count($file_fields);
        }

        if ($this->checkAccessField('customization_text_labels')) {
            $fields['text_fields'] = count($text_fields);
        }

        Db::getInstance()->execute(
            'UPDATE '._DB_PREFIX_.'product p, '._DB_PREFIX_.'product_shop ps
            SET p.`uploadable_files` = ('.$fields['uploadable_files'].' + p.`uploadable_files`),
                p.`text_fields` =('.$fields['text_fields'].' + p.`text_fields`),
                ps.`uploadable_files` = ('.$fields['uploadable_files'].' + ps.`uploadable_files`),
                ps.`text_fields` =('.$fields['text_fields'].' + ps.`text_fields`)
            WHERE p.`id_product` = ps.`id_product`
            AND p.`id_product` IN('.implode(',', array_map('intval', $products)).')'
        );

        foreach ($products as $id_product) {
            $has_required_fields = 0;

            if ($this->checkAccessField('customization_file_labels')) {
                foreach ($file_fields as $file_field) {
                    $customization_field = new CustomizationField();
                    $customization_field->id_product = $id_product;
                    $customization_field->type = Product::CUSTOMIZE_FILE;
                    if (array_key_exists('required', $file_field)) {
                        $customization_field->required = (int)$file_field['required'];
                    } else {
                        $customization_field->required = false;
                    }
                    $has_required_fields |= $customization_field->required;
                    $customization_field->name = $file_field['name'];
                    $customization_field->save();
                }
            }

            if ($this->checkAccessField('customization_text_labels')) {
                foreach ($text_fields as $text_field) {
                    $customization_field = new CustomizationField();
                    $customization_field->id_product = $id_product;
                    $customization_field->type = Product::CUSTOMIZE_TEXTFIELD;
                    if (array_key_exists('required', $text_field)) {
                        $customization_field->required = (int)$text_field['required'];
                    } else {
                        $customization_field->required = false;
                    }
                    $has_required_fields |= $customization_field->required;
                    $customization_field->name = $text_field['name'];
                    $customization_field->save();
                }
            }

            if ($has_required_fields) {
                ObjectModel::updateMultishopTable(
                    'product',
                    array('customizable' => 2),
                    'a.id_product = '.(int)$id_product
                );
            }
            Configuration::updateGlobalValue(
                'PS_CUSTOMIZATION_FEATURE_ACTIVE',
                Customization::isCurrentlyUsed()
            );
        }

        return array();
    }

    public function applyChangeBoth($products, $combinations)
    {
    }

    public function getTitle()
    {
        return $this->l('Customization fields');
    }
}
