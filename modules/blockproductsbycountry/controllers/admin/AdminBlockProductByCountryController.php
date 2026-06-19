<?php
/**
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminBlockProductByCountryController extends AdminController
{
    public $module;
    public $fields_list;
    protected $_defaultOrderBy = 'id_country';
    protected $_defaultOrderWay = 'ASC';
    public $id_country = 0;
    public $obj;
    
    public function __construct()
    {
        $this->moduleObj = Module::getInstanceByName('blockproductsbycountry');
        $this->context = Context::getContext();
        if ((int)Tools::getValue('id_country')) {
            $id_country = (int)Tools::getValue('id_country');
            $this->obj = new Country($id_country, $this->context->language->id);
        }
        
        $this->bootstrap = true;
        $this->required_database = false;
        $this->className = 'Blockproductsbycountry';
        $this->table = 'bpbc_categories';
        $this->identifier = 'id_country';
        $this->lang = false;
        $this->explicitSelect = true;

        $this->allow_export = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Are you sure you want to delete all rules for this country?'),
                'icon' => 'icon-trash'
            )
        );

        $this->default_form_language = $this->context->language->id;

        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_country' => array(
                'title' => $this->l('Country ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'search' => true,
                'filter_type' => 'int',
                'filter_key' => 'cl!id_country',
            ),
            'name' => array(
                'title' => $this->l('Country'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'havingFilter' => false,
                'filter_key' => 'cl!name',
            ),
            'no_products' => array(
                'title' => $this->l('Products blocked'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'filter_type' => 'int',
                'havingFilter' => true,
            ),
            'no_categories' => array(
                'title' => $this->l('Categories blocked'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'filter_type' => 'int',
                'havingFilter' => true,
            ),
            'no_manufacturers' => array(
                'title' => $this->l('Manufacturers blocked'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'filter_type' => 'int',
                'havingFilter' => true,
            ),
            'no_suppliers' => array(
                'title' => $this->l('Suppliers blocked'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'filter_type' => 'int',
                'havingFilter' => true,
            ),
        );
        
        $this->_join = 'RIGHT JOIN `'._DB_PREFIX_.'country_lang` cl ON (cl.`id_country` = a.`id_country` AND  cl.`id_lang` = "'.(int)$this->context->language->id.'")';
        $this->_select = '
            (SELECT COUNT(`id_product`) FROM `'._DB_PREFIX_.'bpbc_products` p WHERE p.`id_country` = cl.`id_country`) as `no_products`,
            (SELECT COUNT(`id_category`) FROM `'._DB_PREFIX_.'bpbc_categories` c WHERE c.`id_country` = cl.`id_country`) as `no_categories`,
            (SELECT COUNT(`id_manufacturer`) FROM `'._DB_PREFIX_.'bpbc_manufacturers` m WHERE m.`id_country` = cl.`id_country`) as `no_manufacturers`,
            (SELECT COUNT(`id_supplier`) FROM `'._DB_PREFIX_.'bpbc_suppliers` s WHERE s.`id_country` = cl.`id_country`) as `no_suppliers`
        ';
        $this->_group = '
            GROUP BY cl.`id_country`
        ';
        $this->shopLinkType = '';
        
        parent::__construct();
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->meta_title = $this->l('Block products by country');
    }
    
    public function renderList()
    {
        $output = $this->moduleObj->getConfigureTpl();
        return $output.parent::renderList();
    }
    
    public function renderForm()
    {
        $this->fields_form = array(
            'legend' => array(
                'title' => sprintf($this->l('Block products by country: %s'), $this->obj->name),
                'icon' => 'icon-user'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Blocked products'),
                    'name' => 'id_product[]',
                    'col' => '4',
                    'options' => array(
                        'query' => $this->moduleObj->getProductsArray(),
                        'id' => 'id_product',
                        'name' => 'name',
                    ),
                    'multiple' => true,
                    'class' => 'bpbc_multiselect',
                    'id' => 'id_product',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Blocked categories'),
                    'name' => 'id_category[]',
                    'col' => '4',
                    'options' => array(
                        'query' => $this->moduleObj->getCategoriesArray(),
                        'id' => 'id_category',
                        'name' => 'name',
                    ),
                    'multiple' => true,
                    'class' => 'bpbc_multiselect',
                    'id' => 'id_category',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Blocked manufacturers'),
                    'name' => 'id_manufacturer[]',
                    'col' => '4',
                    'options' => array(
                        'query' => $this->moduleObj->getManufacturersArray(),
                        'id' => 'id_manufacturer',
                        'name' => 'name',
                    ),
                    'multiple' => true,
                    'class' => 'bpbc_multiselect',
                    'id' => 'id_manufacturer',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Blocked suppliers'),
                    'name' => 'id_supplier[]',
                    'col' => '4',
                    'options' => array(
                        'query' => $this->moduleObj->getSuppliersArray(),
                        'id' => 'id_supplier',
                        'name' => 'name',
                    ),
                    'multiple' => true,
                    'class' => 'bpbc_multiselect',
                    'id' => 'id_supplier',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_country'
                ),
            )
        );
        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
        );
        $id_country = (int)Tools::getValue('id_country');
        $this->fields_value = array(
            'id_product[]' => $this->moduleObj->getBlockedProductsByCountry($id_country),
            'id_category[]' => $this->moduleObj->getBlockedCategoriesByCountry($id_country),
            'id_manufacturer[]' => $this->moduleObj->getBlockedManufacturersByCountry($id_country),
            'id_supplier[]' => $this->moduleObj->getBlockedSuppliersByCountry($id_country),
            'id_country' => (int)Tools::getValue('id_country'),
        );
        $output = $this->moduleObj->getConfigureTpl();
        return $output.parent::renderForm();
    }
    
    public function processUpdate()
    {
        if (Tools::getValue('id_country')) {
            $id_country = (int)Tools::getValue('id_country');
            $selected = array();
            $selected['products'] = Tools::getValue('id_product');
            $selected['categories'] = Tools::getValue('id_category');
            $selected['manufacturers'] = Tools::getValue('id_manufacturer');
            $selected['suppliers'] = Tools::getValue('id_supplier');
            if ($this->moduleObj->updateCountryRules($id_country, $selected)) {
                $this->context->smarty->assign('conf', $this->l('Successful update.'));
            } else {
                $this->errors[] = $this->l('An error occured.');
            }
        }
    }
    
    public function processDelete()
    {
        if ($id_country = Tools::getValue('id_country')) {
            if ($this->moduleObj->updateCountryRules((int)$id_country)) {
                $this->context->smarty->assign('conf', $this->l('Successful update.'));
            } else {
                $this->errors[] = $this->l('An error occured.');
            }
        }
    }
    
    public function processBulkDelete()
    {
        if ($countries = Tools::getValue('bpbc_categoriesBox')) {
            foreach ($countries as $id_country) {
                if (!$this->moduleObj->updateCountryRules((int)$id_country)) {
                    $this->errors[] = $this->l('An error occured.');
                    return false;
                }
            }
            $this->context->smarty->assign('conf', $this->l('Successful update.'));
        }
    }
    
    public function displayDeleteLink($token = null, $id_category = 0, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_delete.tpl');

        $tpl->assign(array(
            'href' => self::$currentIndex.'&'.$this->identifier.'='.$id_category.'&delete'.$this->table.'&token='.($token != null ? $token : $this->token),
            'confirm' => $this->l('Are you sure you want to delete all the rules of this category?'),
            'action' => $this->l('Delete'),
            'id' => $id_category,
        ));

        return $tpl->fetch();
    }

    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        return Translate::getModuleTranslation('blockproductsbycountry', $string, get_class($this));
    }
}
