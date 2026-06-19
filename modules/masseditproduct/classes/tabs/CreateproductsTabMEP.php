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

class CreateproductsTabMEP extends BaseTabMEP
{
    public function applyChangeBoth($products, $combinations)
    {
    }

    public function applyChangeForProducts($products)
    {
    }

    public function apply()
    {
        $result = array();
        $name_lang_default = pSQL(Tools::getValue('name_'.$this->context->language->id));
        if (!$name_lang_default) {
            $name_lang_default = pSQL(Tools::getValue('name_'.Configuration::get('PS_LANG_DEFAULT')));
        }

        if (!$name_lang_default) {
            foreach (${'_POST'} as $key => $post) {
                if (Tools::substr($key, 0, 5) == 'name_' && !empty($post)) {
                    $name_lang_default = $post;
                }
            }
        }

        if (empty($name_lang_default)) {
            die('Empty product name.');
        }

        $id_attribute_group = (int)Tools::getValue('attribute');
        $attributes = Db::getInstance()->executeS(
            'SELECT * FROM `'._DB_PREFIX_.'attribute` 
		WHERE `id_attribute_group` ='.(int)$id_attribute_group
        );

        if (!$attributes) {
            $attributes = array(array());
        }

        foreach ($attributes as $attribute) {
            $product = new Product();
            $name = array();
            $languages = Language::getLanguages(false);
            $language_ids = array();
            foreach ($languages as $language) {
                $language_ids[] = (int)$language['id_lang'];
            }

            foreach ($language_ids as $id_lang) {
                if (count($attribute)) {
                    $attribute_group = new AttributeGroup($id_attribute_group, $id_lang);
                    $attribute_obj = new Attribute($attribute['id_attribute']);
                }

                $post_name_lang = Tools::getValue('name_'.$id_lang);
                $name_lang = empty($post_name_lang)
                    ? $name_lang_default : pSQL(Tools::getValue('name_'.$id_lang));
                $name[$id_lang] = $name_lang.
                    (count($attribute) ? '-'.$attribute_group->name.': '.$attribute_obj->name[$id_lang] : '');
            }

            $product->name = $name;
            foreach ($product->name as $id_lang => $name_lang) {
                $product->link_rewrite[$id_lang] = Tools::link_rewrite($name_lang);
            }

            if (Tools::getIsset('unit_price') != null) {
                $product->unit_price = str_replace(',', '.', Tools::getValue('unit_price'));
            }
            $product->id_category_default = (int)Tools::getValue('id_category_default');

            if (!$product->add()) {
                die('An error occurred while creating an object.');
            }
            $product->addToCategories(Tools::getValue('categoryBox'));
        }

        if (LoggerMEP::getInstance()->hasError()) {
            return array();
        } else {
            $this->reindexSearch();
            return $result;
        }
    }

    public function applyChangeForCombinations($products)
    {
    }

    public function getTitle()
    {
        return $this->l('Create products');
    }

    public function getAttributes()
    {
        return array('data-action' => 'create_products');
    }

    public function renderFormCreateProducts()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => '',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Name').':',
                        'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}',
                        'name' => 'name',
                        'class' => 'class',
                        'lang' => true,
                        'required' => true,
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Attribute for name suffix').':',
                        'hint' => $this->l('The name of the attribute will be added to the title'),
                        'name' => 'attribute',
                        'options' => array(
                            'default' => array('value' => 0, 'label' => '--'),
                            'query' => AttributeGroup::getAttributesGroups($this->context->language->id),
                            'id' => 'id_attribute_group',
                            'name' => 'name',
                        ),
                    ),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->override_folder = 'override/';

        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
        $this->fields_form = array();

        $languages = ToolsModuleMEP::getLanguages(false);
        $name = array();
        foreach ($languages as $language) {
            $name[$language['id_lang']] = '';
        }

        $helper->tpl_vars = array(
            'fields_value' => array('name' => $name, 'attribute' => ''),
            'languages' => $languages,
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function assignVariables()
    {
        $variables = parent::assignVariables();
        $variables['form_create_products'] = $this->renderFormCreateProducts();
        $variables['currency2'] = $this->context->currency;

        return $variables;
    }
}
