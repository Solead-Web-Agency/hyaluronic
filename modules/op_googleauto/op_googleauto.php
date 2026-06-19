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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Op_googleauto extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'op_googleauto';
        $this->tab = 'others';
        $this->version = '1.4.3';
        $this->author = 'Open Presta';
        $this->need_instance = 0;
        $this->module_key =  '100e47c6639576624e7990bff209ebb8';
        $this->addons_id = '16496'; 



        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Easy Google Address Autocomplete');
        $this->description = $this->l('Enhance your Prestashop checkout and user experience with our Google address auto complete module');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionAdminControllerSetMedia') &&
            $this->registerHook('displayHeader');
    }

    public function uninstall()
    {
   

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
      
        /**
         * If values have been submitted in the form, process.
         */

        $output = '';

        if (((bool)Tools::isSubmit('submitOp_googleautoModule')) == true) {
              $output .= $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        if (isset($this->module_key) && $this->module_key) {
            $json_modules = $this->opJsonModuleFile();
        } else {
            $json_modules = null;
        }

        $this->context->smarty->assign(
            array(
                'addons_id' => $this->addons_id,
                'img_path' => $this->_path.'views/img/',
                'base_link' => $this->context->shop->getBaseURI(),
                'languages' => Language::getLanguages(false),
                'description' => $this->description,
                'author' => $this->author,
                'name' => $this->name,
                'version' => $this->version,
                'iso_code' => $this->isoCode(),
                'iso_domain' => $this->isoCode(true),
                'id_active_lang' => $this->context->language->id,
                'json_modules' => $json_modules,
            )
        );


        return $output.$this->renderForm().$this->context->smarty->fetch($this->local_path . 'views/templates/admin/addons.tpl');
    }
    public function isoCode($domain = false)
    {
        $iso = $this->context->language->iso_code;

        if ($iso == 'fr') {
            return 'fr';
        } elseif ($domain) {
            return 'com';
        } else {
            return 'en';
        }
    }

    public function opJsonModuleFile()
    {
        $conf = Configuration::getMultiple(array('OP_JSON_TIME', 'OP_JSON_FILE'));

        if (!isset($conf['OP_JSON_TIME']) || $conf['OP_JSON_TIME'] < (time() - 604800)) {
            Configuration::updateValue('OP_JSON_TIME', time());
            $url_api = 'https://api-addons.prestashop.com/'
                ._PS_VERSION_.'/contributor/all_products/'
                .$this->module_key.'/'
                .$this->context->language->iso_code.'/'
                .$this->context->country->iso_code;
            $conf['OP_JSON_FILE'] = Tools::file_get_contents($url_api);
            Configuration::updateValue('OP_JSON_FILE', $conf['OP_JSON_FILE']);
        }

        $modules = Tools::jsonDecode($conf['OP_JSON_FILE'], true);

        if (!is_array($modules) || isset($modules['errors'])) {
            Configuration::updateValue('OP_JSON_TIME', 0);
            return null;
        } else {
            return $modules;
        }
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
        $helper->submit_action = 'submitOp_googleautoModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {

        $freearray  = array();
        $groups = Language::getLanguages();
  
        foreach ($groups as $group) {
            $freearray[] = array(
                'type' => 'text',
                'label' => $this->l('Entre iso Lan google for ').$group['name'],
                'name' => 'OP_GOOGLEAUTO_language_'.$group['id_lang'],
            );
        }


        $freecountry  = array();
        $groups = Country::getCountries($this->context->language->id, true);
        foreach ($groups as $group) {
            $freecountry[] = array(
                'type' => 'text',
                'label' => $this->l('Address Format For country :  ') . $group['name'],
                'name' => 'OP_GOOGLEAUTO_format_county_' . Tools::strtoupper($group['iso_code']),
                'desc' => $this->l('put your address format if you want to change the default :  {street} , {route} ,{sublocality_level_1} , {locality} , {administrative_area_level_1}, {administrative_area_level_2} ,{postal_code} '),

            );
        }

        $form = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter a Map Key (Include place API)'),
                        'name' => 'OP_GOOGLEAUTO_Key',
                        'label' => $this->l('Google Key '),
                    ),
               

                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'OP_GOOGLEAUTO_restricttopays',
                        'label' => $this->l('restrict to pays'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'OP_GOOGLEAUTO_restricttopostcode',
                        'label' => $this->l('restrict to post code'),
                    ),
                   /* array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'OP_GOOGLEAUTO_language',
                        'label' => $this->l('language'),
                    ),*/
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $form['form']['input'] = array_merge($form['form']['input'],$freearray);
        $form['form']['input'] = array_merge($form['form']['input'], $freecountry);

return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $groups = Language::getLanguages();
        $values = array();
        foreach ($groups as $group) {
            $values['OP_GOOGLEAUTO_language_'.$group['id_lang']] =  Configuration::get('OP_GOOGLEAUTO_language_'.$group['id_lang']);
        } 
        $groups = Country::getCountries($this->context->language->id, true);
        foreach ($groups as $group) {
            $values['OP_GOOGLEAUTO_format_county_'. Tools::strtoupper($group['iso_code'])] =  Configuration::get('OP_GOOGLEAUTO_format_county_'. Tools::strtoupper($group['iso_code']));

       
       
        }

        $final_value =  array(
      
            'OP_GOOGLEAUTO_Key' => Configuration::get('OP_GOOGLEAUTO_Key'),
           
            'OP_GOOGLEAUTO_restricttopays' => Configuration::get('OP_GOOGLEAUTO_restricttopays'),
            'OP_GOOGLEAUTO_restricttopostcode' => Configuration::get('OP_GOOGLEAUTO_restricttopostcode'),

            'OP_GOOGLEAUTO_language' => Configuration::get('OP_GOOGLEAUTO_language'),
            
            
        );
        $final_value = array_merge($final_value,$values);
        return $final_value;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $html = '';
        $this->_errors = array();
        $confirmation = 6;


        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if(Tools::getValue($key) == '' && $key=='OP_GOOGLEAUTO_Key')$this->_errors[] = sprintf($this->l('The "%s" field is invalid'), $key);
            Configuration::updateValue($key, Tools::getValue($key));
        }
        if (!sizeof($this->_errors)) {
            $token = Tools::getAdminTokenLite('AdminModules');
            $redirect_url = 'index.php?tab=AdminModules&configure=' .
            $this->name . '&token=' . $token . '&conf='.$confirmation;
            Tools::redirectAdmin($redirect_url);
        }elseif (sizeof($this->_errors)) {
            foreach ($this->_errors as $err) {
                $html .= $this->displayError($err);
            }
        }

        return $html;

    }

  
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $controller = Dispatcher::getInstance()->getController();

        if (in_array($controller, ['address','order'])) {

            $groups = Country::getCountries($this->context->language->id, true);
            $country = array();
            foreach ($groups as $group) {
    
                $country['opgg_format_county_'. Tools::strtoupper($group['iso_code'])] 
                = Configuration::get('OP_GOOGLEAUTO_format_county_'. Tools::strtoupper($group['iso_code']));

              
           
            }

            
        Media::addJsDef(array(
  
            'opggApiKey' => Configuration::get('OP_GOOGLEAUTO_Key'),
            'opcheckcountry' => $this->l('Please check your address , this country {country} not available to delivery'),
            'opggApirestricttopays' => Configuration::get('OP_GOOGLEAUTO_restricttopays'),
            'opggApirestricttopostcode' => Configuration::get('OP_GOOGLEAUTO_restricttopostcode'),

            'opggApilanguage' => Configuration::get('OP_GOOGLEAUTO_language_'.$this->context->language->id),
            'oppgg_format' => $country,
 
        ));

        $this->context->controller->addJS($this->_path.'views/js/front.js');
    }
    }

    public function hookDisplayHeader()
    {
       return $this->hookHeader();
    }

    public function hookBackOfficeHeader()
    {

        
        $controller = Dispatcher::getInstance()->getController();

        if (in_array($controller, ['AdminAddresses'])) {

            $groups = Country::getCountries($this->context->language->id, true);
            $country = array();
            foreach ($groups as $group) {
    
                $country['opgg_format_county_'. Tools::strtoupper($group['iso_code'])] 
                = Configuration::get('OP_GOOGLEAUTO_format_county_'. Tools::strtoupper($group['iso_code']));

              
           
            }


           
            Media::addJsDef(array(
  
                'opggApiKey' => Configuration::get('OP_GOOGLEAUTO_Key'),
                'opcheckcountry' => $this->l('Please check your address , this country {country} not available to delivery'),
                'opggApirestricttopays' => Configuration::get('OP_GOOGLEAUTO_restricttopays'),
                'opggApirestricttopostcode' => Configuration::get('OP_GOOGLEAUTO_restricttopostcode'),

                'opggApilanguage' => Configuration::get('OP_GOOGLEAUTO_language'),
                'oppgg_format' => $country,

    
            ));
    
            $this->context->controller->addJS($this->_path.'views/js/back.js');
        }
    }

    public function hookactionAdminControllerSetMedia(){
        $controller = Dispatcher::getInstance()->getController();

        if ((int) Tools::getValue('liteDisplaying') && in_array($controller, ['AdminAddresses'])) {
            $groups = Country::getCountries($this->context->language->id, true);
            $country = array();
            foreach ($groups as $group) {
    
                $country['opgg_format_county_'. Tools::strtoupper($group['iso_code'])] 
                = Configuration::get('OP_GOOGLEAUTO_format_county_'. Tools::strtoupper($group['iso_code']));

              
           
            }


            Media::addJsDef(array(
  
                'opggApiKey' => Configuration::get('OP_GOOGLEAUTO_Key'),
                'opcheckcountry' => $this->l('Please check your address , this country {country} not available to delivery'),
                'opggApirestricttopays' => Configuration::get('OP_GOOGLEAUTO_restricttopays'),
                'opggApirestricttopostcode' => Configuration::get('OP_GOOGLEAUTO_restricttopostcode'),

                'opggApilanguage' => 'OP_GOOGLEAUTO_language_'.$this->context->language->id,
                'oppgg_format' => $country,

    
            ));
    
            $this->context->controller->addJS($this->_path.'views/js/back.js');
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        return $this->hookBackOfficeHeader();
    }


}
