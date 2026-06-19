<?php
/**
 * OrderEdit
 *
 * @author    silbersaiten <info@silbersaiten.de>
 * @copyright 2022 silbersaiten
 * @license   See joined file licence.txt
 * @support   silbersaiten <support@silbersaiten.de>
 * @category  Module
 * @version   2.0.35
 * @link      https://www.silbersaiten.de
 */

class OrderEdit extends Module
{
    public function __construct()
    {
        $this->name = 'orderedit';
        $this->version = '2.0.35';
        $this->tab = 'quick_bulk_update';
        $this->author = 'Silbersaiten';
        $this->module_key = '6b872e4b6176bf9d6ec905e489a84ada';

        parent::__construct();

        $this->displayName = $this->l('Order Editor');
        $this->description = $this->l('Order editor since PrestaShop 1.7.7.* versions');
        $this->ps_versions_compliancy = array('min' => '1.7.7.1', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        require_once __DIR__ . '/install/install_sql.php';
        return (
            parent::install() &&
            $this->createTab("AdminParentOrders", "AdminOrderEdit", "Order Editor") &&
            $this->createTab('AdminOrders', 'AdminOrderEditAjax', 'Admin Order Edit Ajax', 0) &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->updateFieldReference(32) &&
            Configuration::updateValue('ORDER_EDIT_SHOW_SEARCH_FIELD', true)
        );
    }

    public function uninstall()
    {
        require_once __DIR__ . '/install/uninstall_sql.php';
        return parent::uninstall() && $this->deleteTab('AdminOrderEdit') && $this->deleteTab('AdminOrderEditAjax');
    }

    /** Calling when the module installing */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function createTab($parent_class_name, $class_name, $name, $active = 0)
    {
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $class_name;
        $tab->name = array();
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        $tab->id_parent = (int)Tab::getIdFromClassName($parent_class_name);
        $tab->module = $this->name;
        return $tab->add();
    }

    private function deleteTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        $tab = new Tab($id_tab);
        return $tab->delete();
    }

    private function updateFieldReference($length)
    {
        return Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'orders` MODIFY COLUMN `reference` VARCHAR(' . $length . ') NULL;'
        );
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (!(int)$this->active) {
            return false;
        }

        if ($this->context->controller->php_self == 'AdminOrders') {
            Media::addJsDef(
                array(
                    'order_edit_round_type' => Configuration::get('PS_ROUND_TYPE'),
                    'order_edit_compute_precision' => _PS_PRICE_COMPUTE_PRECISION_,
                    'admin_module_controller' => $this->context->link->getAdminLink('AdminOrderEdit'),
                    'search_field' => $this->l('Find the order'),
                    'search_field_placeholder' => $this->l('type the order name or product name'),
                    'show_search_field' => (int)Configuration::get('ORDER_EDIT_SHOW_SEARCH_FIELD'),
                    'order_edit_admin_link' => $this->context->link->getAdminLink('AdminOrderEdit', true),
                )
            );

            $this->context->controller->addJqueryPlugin(array('autocomplete', 'typewatch', 'growl'));
            $this->context->controller->addJS($this->_path . '/views/js/order_edit_list.js');
            $this->context->controller->addJS($this->_path . '/views/js/order_edit_searcher.js');
            $this->context->controller->addJS($this->_path . '/views/js/order_editor.js');
            $this->context->controller->addCSS($this->_path . '/views/css/order_edit.css');
        }
    }

    public function getContent()
    {
        if (Tools::getValue('submitOrderEditConfig')) {
            $show_search_field = Tools::getValue('show_search_field', false);
            Configuration::updateValue('ORDER_EDIT_SHOW_SEARCH_FIELD', $show_search_field);
        }

        $this->context->smarty->assign(
            array(
                'image_module_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/',
                'show_search_field' => (int)Configuration::get('ORDER_EDIT_SHOW_SEARCH_FIELD'),
            )
        );
        $this->bootstrap = true;
        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . $this->name . '/views/templates/admin/configuration.tpl');
    }
}
