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

class AdminBlockProductByCategoryController extends AdminController
{
    public $module;
    public $fields_list;
    protected $_defaultOrderBy = 'id_category';
    protected $_defaultOrderWay = 'ASC';
    public $id_category = 0;
    public $obj;
    
    public function __construct()
    {
        $this->moduleObj = Module::getInstanceByName('blockproductsbycountry');
        $this->context = Context::getContext();
        if ((int)Tools::getValue('id_category')) {
            $id_category = (int)Tools::getValue('id_category');
            $this->obj = new Category($id_category, $this->context->language->id);
        }
        
        $this->bootstrap = true;
        $this->required_database = false;
        $this->className = 'Blockproductsbycountry';
        $this->table = 'bpbc_categories';
        $this->identifier = 'id_category';
        $this->lang = false;
        $this->explicitSelect = true;

        $this->allow_export = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Are you sure you want to delete all rules for this category?'),
                'icon' => 'icon-trash'
            )
        );

        $this->default_form_language = $this->context->language->id;

        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_category' => array(
                'title' => $this->l('Category ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'search' => true,
                'filter_type' => 'int',
                'filter_key' => 'cl!id_category',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'havingFilter' => false,
                'filter_key' => 'cl!name',
            ),
            'no_countries' => array(
                'title' => $this->l('Countries blocked'),
                'align' => 'text-left',
                'width' => 'auto',
                'search' => true,
                'filter_type' => 'int',
                'havingFilter' => true,
            ),
        );
        
        $this->_join = 'RIGHT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = a.`id_category` AND cl.`id_shop` = "'.(int)$this->context->shop->id.'" AND cl.`id_lang` = "'.(int)$this->context->language->id.'")';
        $this->_select = '
            COUNT(a.`id_country`) as `no_countries`
        ';
        $this->_group = '
            GROUP BY cl.`id_category`
        ';
        $this->shopLinkType = '';
        
        parent::__construct();
    }
    
    public function initContent()
    {
        parent::initContent();
        
        $this->meta_title = $this->l('Block products by category');
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
                'title' => sprintf($this->l('Block products by category: %s'), $this->obj->name),
                'icon' => 'icon-user'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Blocked countries'),
                    'name' => 'id_country[]',
                    'col' => '4',
                    'options' => array(
                        'query' => $this->moduleObj->getCountriesArray(),
                        'id' => 'id_country',
                        'name' => 'name',
                    ),
                    'multiple' => true,
                    'class' => 'bpbc_multiselect',
                    'id' => 'id_country',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_category'
                ),
            )
        );
        $this->fields_form['submit'] = array(
            'title' => $this->l('Save'),
        );
        $id_category = (int)Tools::getValue('id_category');
        $this->fields_value = array(
            'id_country[]' => $this->moduleObj->getBlockedCountriesByCategory($id_category),
            'id_category' => (int)Tools::getValue('id_category'),
        );
        $output = $this->moduleObj->getConfigureTpl();
        return $output.parent::renderForm();
    }
    
    public function processUpdate()
    {
        if (Tools::getValue('id_category')) {
            $id_category = (int)Tools::getValue('id_category');
            $selected_countries = Tools::getValue('id_country');
            if ($this->moduleObj->updateCategoryRules($id_category, $selected_countries)) {
                $this->context->smarty->assign('conf', $this->l('Successful update.'));
            } else {
                $this->errors[] = $this->l('An error occured.');
            }
        }
    }
    
    public function processDelete()
    {
        if ($id_category = Tools::getValue('id_category')) {
            if ($this->moduleObj->updateCategoryRules((int)$id_category)) {
                $this->context->smarty->assign('conf', $this->l('Successful update.'));
            } else {
                $this->errors[] = $this->l('An error occured.');
            }
        }
    }
    
    public function processBulkDelete()
    {
        if ($categories = Tools::getValue('bpbc_categoriesBox')) {
            foreach ($categories as $id_category) {
                if (!$this->moduleObj->updateCategoryRules((int)$id_category)) {
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
