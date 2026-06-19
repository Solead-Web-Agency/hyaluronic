<?php

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
/**
 * Project : everpsminimumorder
 * @author Team Ever
 * @copyright Team Ever
 * @license   Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 * @link https://www.team-ever.com
 */

class EverPsMinimumOrder extends Module
{
    private $html;
    private $postErrors = array();
    private $postSuccess = array();
    
    public function __construct()
    {
        $this->name = 'everpsminimumorder';
        $this->tab = 'front_office_features';
        $this->version = '2.5.1';
        $this->author = 'Ben @TheDigitalFactory';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '90260ff54877e3b1d9871cf01d219d57';

        parent::__construct();
        $this->isSeven = Tools::version_compare(_PS_VERSION_, '1.7', '>=') ? true : false;
        $this->displayName = $this->l('Ever Minimum Order');
        $this->description = $this->l('Set minimal amount required to order');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module ?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');
        Configuration::updateValue('EVERMINIMUM_PER_GROUP', true);
        Configuration::updateValue('EVERMINIMUM_CURRENCY', (int) Configuration::get('PS_CURRENCY_DEFAULT'));
        return parent::install()
            && $this->installModuleTab(
                'AdminEverMinimumOrder',
                'AdminParentOrders',
                $this->l('Minimal order quantity')
            ) && $this->registerHook('actionPresentCart') ;
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallModuleTab('AdminEverMinimumOrder')
            && $this->uninstallDB();
    }

    private function installModuleTab($tabClass, $parent, $tabName)
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = $tabClass;
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->position = Tab::getNewLastPosition($tab->id_parent);
        $tab->module = $this->name;
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int)$lang['id_lang']] = $tabName;
        }
        return $tab->add();
    }

    private function uninstallModuleTab($tabClass)
    {
        $tab = new Tab((int)Tab::getIdFromClassName($tabClass));
        return $tab->delete();
    }

    public function uninstallDB()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'everpsminimumorder`');
        if ($res == 0 || !parent::uninstall()) {
            return false;
        }
        return true;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        if (((bool)Tools::isSubmit('saveEverMinimumOrder')) == true) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            }
        }
        if (count($this->postErrors)) {
            foreach ($this->postErrors as $error) {
                $this->html .= $this->displayError($error);
            }
        }
        if (count($this->postSuccess)) {
            foreach ($this->postSuccess as $success) {
                $this->html .= $this->displayConfirmation($success);
            }
        }
        $link = new Link();
        
        $this->context->smarty->assign(array(
            'minorderimg_dir' => $this->_path,
            'minorderimg_url' => $link->getAdminLink('AdminEverMinimumOrder'),
        ));

        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/header.tpl');
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $this->html .= $this->renderForm();
        $this->html .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/footer.tpl');

        return $this->html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'saveEverMinimumOrder';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        $currencies = Currency::getCurrencies(false, false);
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-smile',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Based on default group'),
                        'desc' => $this->l('Are rules based on customer default group ?'),
                        'hint' => $this->l('Else first group rule found will be applied'),
                        'name' => 'EVERMINIMUM_PER_GROUP',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Please confirm default currency'),
                        'hint' => $this->l('For currency conversion'),
                        'desc' => $this->l('Will be default currency for minimum rules'),
                        'name' => 'EVERMINIMUM_CURRENCY',
                        'identifier' => 'name',
                        'required' => true,
                        'options' => array(
                            'query' => $currencies,
                            'id' => 'id_currency',
                            'name' => 'sign',
                        ),
                    ),
                    array(
                        'type' => 'textarea',
                        'lang' => true,
                        'label' => $this->l('Custom message on cart page'),
                        'desc' => $this->l('Please add custom cart page message'),
                        'hint' => $this->l('Use PS_PURCHASE_MINIMUM text to shown minimum purchase amount'),
                        'name' => 'EVERMINIMUM_MSG',
                        'required' => false,
                        'autoload_rte' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        $msg = array();
        foreach (Language::getLanguages(false) as $lang) {
            $msg[$lang['id_lang']] = (
                Tools::getValue('EVERMINIMUM_MSG_'
                    .$lang['id_lang'])
            ) ? Tools::getValue(
                'EVERMINIMUM_MSG_'
                .$lang['id_lang']
            ) : '';
        }
        return array(
            'EVERMINIMUM_PER_GROUP' => Configuration::get('EVERMINIMUM_PER_GROUP'),
            'EVERMINIMUM_CURRENCY' => Configuration::get('EVERMINIMUM_CURRENCY'),
            'EVERMINIMUM_MSG' => (!empty(
                $msg[(int)Configuration::get('PS_LANG_DEFAULT')]
            )) ? $msg : Configuration::getInt(
                'EVERMINIMUM_MSG'
            ),
        );
    }

    public function postValidation()
    {
        if (((bool)Tools::isSubmit('saveEverMinimumOrder')) == true) {
            if (Tools::getValue('EVERMINIMUM_PER_GROUP')
                && !Validate::isBool(Tools::getValue('EVERMINIMUM_PER_GROUP'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Rules per group" is not valid'
                );
            }
            if (!Tools::getValue('EVERMINIMUM_CURRENCY')
                && !Validate::isBool(Tools::getValue('EVERMINIMUM_CURRENCY'))
            ) {
                $this->postErrors[] = $this->l(
                    'Error : The field "Default currency" is not valid'
                );
            }
            // Multilingual validation
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('EVERMINIMUM_MSG_'.$lang['id_lang'])
                    && !Validate::isCleanHtml(Tools::getValue('EVERMINIMUM_MSG_'.$lang['id_lang']))
                ) {
                    $this->postErrors[] = $this->l(
                        'Error: message is not valid for lang '
                    ).$lang['iso_code'];
                }
            }
        }
    }

    protected function postProcess()
    {
        $this->registerHook('shoppingCartFooter');
        $this->registerHook('displayReassurance');
        $msg = array();
        foreach (Language::getLanguages(false) as $lang) {
            $msg[$lang['id_lang']] = (
                Tools::getValue('EVERMINIMUM_MSG_'
                    .$lang['id_lang'])
            ) ? Tools::getValue(
                'EVERMINIMUM_MSG_'
                .$lang['id_lang']
            ) : '';
        }
        Configuration::updateValue(
            'EVERMINIMUM_MSG',
            $msg,
            true
        );
        Configuration::updateValue(
            'EVERMINIMUM_PER_GROUP',
            Tools::getValue('EVERMINIMUM_PER_GROUP')
        );
        Configuration::updateValue(
            'EVERMINIMUM_CURRENCY',
            Tools::getValue('EVERMINIMUM_CURRENCY')
        );
        $this->postSuccess[] = $this->l('All settings have been saved');
    }

    public function getErrorMessage()
    {
        $errorMessage = $this->l('A minimum purchase total of %1s is required to validate your order');
        return $errorMessage;
    }

    public function hookActionPresentCart($params){
        include_once(_PS_MODULE_DIR_.'everpsminimumorder/classes/EverMinimumOrderClass.php');
            
        $context = Context::getContext();
        if ((bool)Configuration::get('EVERMINIMUM_PER_GROUP') === true) {
            $id_group = Customer::getDefaultGroupId(
                (int)$context->customer->id
            );
            $everMinimumPurchase = EverMinimumOrderClass::getRuleByShopCountryGroup(
                (int)$context->shop->id,
                (int)Customer::getCurrentCountry((int)$context->customer->id),
                (int)Customer::getDefaultGroupId((int)$id_group || (int)$context->customer->id_default_group)
            );
        } else {
            $customer_groups = Customer::getGroupsStatic(
                (int)$context->customer->id
            );
            foreach ($customer_groups as $group) {
                $rule = EverMinimumOrderClass::getRuleByShopCountryGroup(
                    (int)$context->shop->id,
                    (int)Customer::getCurrentCountry((int)$context->customer->id),
                    (int)$group
                );
                if (Validate::isLoadedObject($rule)) {
                    $everMinimumPurchase = $rule;
                }
            }
        }
        
        if ($everMinimumPurchase || Validate::isLoadedObject($everMinimumPurchase)) {
            if ($everMinimumPurchase->use_tax) {
                $tax = 1 + (float)$context->cart->getAverageProductsTaxRate();
                $amount = (float)$everMinimumPurchase->amount / (float)$tax;
            } else {
                $amount = (float)$everMinimumPurchase->amount;
            }
            if ((int)Configuration::get('EVERMINIMUM_CURRENCY') != (int)$context->cart->id_currency) {
                $minimalPurchase = Tools::convertPrice(
                    $amount,
                    Currency::getCurrency((int)Configuration::get('EVERMINIMUM_CURRENCY')),
                    false
                );
            } else {
                $minimalPurchase = Tools::convertPrice(
                    $amount,
                    Currency::getCurrency((int)$context->cart->id_currency),
                    false
                );
            }

            $priceFormatter = new PriceFormatter();

            $params['presentedCart']['minimalPurchaseRequired'] = ($params['presentedCart']['totals']['total_excluding_tax']['amount'] > $minimalPurchase) ?
             $this->l('You have reached the maximum order limit of 690 €. If you wish to order more, you will have to place a new order.') :
            '';
        }
    }
}
