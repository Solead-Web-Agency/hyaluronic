<?php
/**
 * 2007-2023 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2023 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Taxexempt extends Module
{
    protected $config_form = false;
    public function __construct()
    {
        $this->name = 'taxexempt';
        $this->tab = 'billing_invoicing';
        $this->version = '1.3.8';
        $this->author = 'MEG Venture';
        $this->need_instance = 0;
        $this->module_key = '3e13284addd010fae1de23142133b8ea';
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Tax Exempt Customer Groups');
        $this->description = $this->l('Customers who are assigned to the specified customer groups are tax-exempt');
        $this->confirmUninstall = $this->l('the specified customer groups will no longer be tax-exempt. Do you confirm?');
    }
    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TAXEXEMPT_GROUPS', null);
        Configuration::updateValue('PS_TAX_DISPLAY', 0);
        if (file_exists(_PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php')) {
            rename(_PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php', _PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator_old.php');
            copy(_PS_MODULE_DIR_ . 'taxexempt/override/src/Adapter/Product/PriceCalculator.php', _PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php');
        } else {
            copy(_PS_MODULE_DIR_ . 'taxexempt/override/src/Adapter/Product/PriceCalculator.php', _PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php');
        }
        if (!parent::install()
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('displayHeader')) {
            return false;
        }
        return true;
    }
    public function uninstall()
    {
        Configuration::deleteByName('TAXEXEMPT_GROUPS');
        if (!parent::uninstall()) {
            return false;
        }
        if (file_exists(_PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator_old.php')) {
            unlink(_PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php');
            rename(_PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator_old.php', _PS_CORE_DIR_ . '/src/Adapter/Product/PriceCalculator.php');
        }
        return true;
    }
    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $this->html = '';
        if (((bool) Tools::isSubmit('submitTaxexemptModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $this->html . $output . $this->renderForm();
    }
    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $groups = Group::getGroups($this->context->language->id, true);
        foreach ($groups as $key => $group) {
            $groups[$key]['label'] = $group['id_group'] . ' ' . $group['name'];
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Tax-Exempt Group Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'checkbox',
                        'name' => 'TAXEXEMPT_GROUPS',
                        'label' => $this->l('Tax Exempt Groups'),
                        'desc' => $this->l('Select all the groups that you would like to apply tax exemption. If you leave all empty, there will be no tax exempted groups.'),
                        'values' => array(
                            'query' => $groups,
                            'id' => 'id_group',
                            'name' => 'label',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTaxexemptModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        $groups = Group::getGroups($this->context->language->id, true);
        foreach ($groups as $key => $group) {
            $groups[$key]['label'] = $group['id_group'] . ' ' . $group['name'];
        }
        return $helper->generateForm(array($fields_form));
    }
    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $config_fields = array();
        //get all groups available
        $groups = Group::getGroups($this->context->language->id, true);
        $id_group = array();
        foreach ($groups as $group) {
            $id_group[] = $group['id_group'];
        }
        //get all groups from $_POST
        $id_group_post = array();
        foreach ($id_group as $id) {
            if (Tools::getValue('TAXEXEMPT_GROUPS_' . (int) $id)) {
                $id_group_post['TAXEXEMPT_GROUPS_' . (int) $id] = true;
            }
        }
        //get all groups from Configuration
        $id_group_config = array();
        if ($confs = Configuration::get('TAXEXEMPT_GROUPS')) {
            $confs = explode(',', Configuration::get('TAXEXEMPT_GROUPS'));
        } else {
            $confs = array();
        }
        foreach ($confs as $conf) {
            $id_group_config['TAXEXEMPT_GROUPS_' . (int) $conf] = true;
        }
        //return only common values and value from post
        if (Tools::isSubmit('submitTaxexemptModule')) {
            $config_fields = array_merge($config_fields, array_intersect($id_group_post, $id_group_config));
        } else {
            $config_fields = array_merge($config_fields, $id_group_config);
        }
        return $config_fields;
    }
    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        $groups = Group::getGroups($this->context->language->id, true);
        $id_group = array();
        foreach ($groups as $group) {
            if (Tools::getValue('TAXEXEMPT_GROUPS_' . (int) $group['id_group'])) {
                $id_group[] = $group['id_group'];
            }
        }
        $updated = Configuration::updateValue('TAXEXEMPT_GROUPS', implode(',', $id_group));
        if ($updated) {
            $this->html = $this->displayConfirmation($this->l('Settings updated successfully.'));
        }
    }
    public function hookActionAdminControllerSetMedia($params)
    {
        $this->context->controller->addJS($this->_path . 'views/js/admin/taxexempt.js');
    }
    public function hookDisplayHeader()
    {
        $context = Context::getContext();
        $id_lang = $context->cart->id_lang;
        $id_customer = $context->customer->id;
        $taxexempts = explode(",", Configuration::get('TAXEXEMPT_GROUPS'));
        $customer_groups = Db::getInstance()->executeS("SELECT " . _DB_PREFIX_ . "customer_group.id_group FROM " . _DB_PREFIX_ . "customer_group
                        LEFT JOIN " . _DB_PREFIX_ . "group_lang ON " . _DB_PREFIX_ . "group_lang.id_group = " . _DB_PREFIX_ . "customer_group.id_group
                        WHERE " . _DB_PREFIX_ . "customer_group.id_customer = '$id_customer' AND " . _DB_PREFIX_ . "group_lang.id_lang = '$id_lang'");
        if (!isset($customer_groups[0]) && !in_array("1", $taxexempts)) {
            $customer_groups = false;
        } else {
            $groups = array();
            foreach ($customer_groups as $k => $v) {
                $groups[$k] = $v['id_group'];
            }
            if (Configuration::get('PS_TAX') == 0) {
                $tax_enabled = 0;
            } elseif (in_array("1", $taxexempts)) {
                $tax_enabled = 0;
            } elseif (count(array_intersect($taxexempts, $groups)) > 0) {
                $tax_enabled = 0;
            } else {
                $tax_enabled = 1;
            }
            //$tax_enabled = ((Configuration::get('PS_TAX') == 0) && (count(array_intersect($taxexempts, $groups)) > 0) && in_array("1", $taxexempts));
            if ($tax_enabled == 0) {
                Media::addJsDef(array(
                    'taxexcl_text' => $this->taxLang()[0],
                    'taxincl_text' => $this->taxLang()[1],
                ));
                $this->context->controller->addJS($this->_path . 'views/js/front/taxexempt.js');
            }
        }
    }
    public function taxLang()
    {
        $iso_code = $this->context->language->iso_code;
        $taxtext = array();
        switch ($iso_code) {
            case "en":
                $taxtext[0] = 'tax excl.';
                $taxtext[1] = 'tax incl.';
                break;
            case "fr":
                $taxtext[0] = 'HT';
                $taxtext[1] = 'TTC';
                break;
            case "de":
                $taxtext[0] = 'o. MwSt.';
                $taxtext[1] = 'inkl. MwSt.';
                break;
            case "nl":
                $taxtext[0] = 'excl. btw';
                $taxtext[1] = 'incl. btw';
                break;
            case "it":
                $taxtext[0] = 'Tasse Escl.';
                $taxtext[1] = 'Iva incl.';
                break;
            case "ca":
                $taxtext[0] = 'sense IVA';
                $taxtext[1] = 'amb IVA';
                break;
            case "es":
                $taxtext[0] = 'Imp. Excl.';
                $taxtext[1] = 'impuestos incl.';
                break;
            case "gl":
                $taxtext[0] = 'Taxas non incl.';
                $taxtext[1] = 'taxas incl.';
                break;
            case "eu":
                $taxtext[0] = 'BEZ gabe';
                $taxtext[1] = 'tax incl.';
                break;
            case "tr":
                $taxtext[0] = 'KDV Hariç';
                $taxtext[1] = 'vergi dahil';
                break;
            case "hr":
                $taxtext[0] = 'bez PDV-a';
                $taxtext[1] = 's PDV-om';
                break;
            default:
                $taxtext[0] = 'tax excl.';
                $taxtext[1] = 'tax incl.';
        }
        return $taxtext;
    }
}
