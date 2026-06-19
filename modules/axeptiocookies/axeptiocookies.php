<?php
/**
 * Developed by Florian @ dectys.com
 *  @author DECTYS SARL <florian@dectys.com>
 *  @copyright  DECTYS SARL
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
**/

if (!defined('_PS_VERSION_')) {
    exit;
}

class AxeptioCookies extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'axeptiocookies';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Axeptio';
        $this->need_instance = 0;
        $this->module_key = '699938714719e6f3e6e697c82c6ccff7';
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Axeptio - Cookies and personal data management');
        $this->description = $this->l('Axeptio - Cookies and personal data management');

        //Axeptio - Cookies et Gestion des données personnelles
        $this->confirmUninstall = $this->l('This will delete the Axeptio cookies module, are you sure ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);


        // true if 1.7, else false
        $this->ps_version = (bool)version_compare(_PS_VERSION_, '1.7', '>=');

        if ($this->ps_version === true) {
            $this->templateFile = 'module:axeptiocookies/views/templates/front/axeptio_cookies.tpl';
        } else {
            $this->templateFile = 'views/templates/front/axeptio_cookies.tpl';
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
            $this->installFixtures() &&
            $this->registerHook('displayFooter');
    }

    protected function installFixtures()
    {
        $languages = Language::getLanguages(false);
        $values = array();
        foreach ($languages as $lang) {
            $id = (int)$lang['id_lang'];
            $values['AXEPTIO_COOKIES_LIVE_MODE'][$id] = '';
            $values['AXEPTIO_COOKIES_ID_KEY'][$id] = '';
            $values['AXEPTIO_COOKIES_VERSION'][$id] = '';
        }
        Configuration::updateValue('AXEPTIO_COOKIES_LIVE_MODE', $values['AXEPTIO_COOKIES_LIVE_MODE']);
        Configuration::updateValue('AXEPTIO_COOKIES_ID_KEY', $values['AXEPTIO_COOKIES_ID_KEY']);
        Configuration::updateValue('AXEPTIO_COOKIES_VERSION', $values['AXEPTIO_COOKIES_VERSION']);
        return true;
    }
    public function uninstall()
    {
        Configuration::deleteByName('AXEPTIO_COOKIES_LIVE_MODE');
        Configuration::deleteByName('AXEPTIO_COOKIES_ID_KEY');
        Configuration::deleteByName('AXEPTIO_COOKIES_VERSION');

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
        if (((bool)Tools::isSubmit('submitAxeptio_cookiesModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $languages = Language::getLanguages(false);
        $values = array();

        $live = 'AXEPTIO_COOKIES_LIVE_MODE';
        $key = 'AXEPTIO_COOKIES_ID_KEY';
        $ver = 'AXEPTIO_COOKIES_VERSION';

        foreach ($languages as $lang) {
            $id = (int)$lang['id_lang'];
            $values[$live][$id] = Tools::getValue($live.'_'.$id, 0);
            $values[$key][$id] = Tools::getValue($key.'_'.$id);
            $values[$ver][$id] = Tools::getValue($ver.'_'.$id);
        }

        Configuration::updateValue('AXEPTIO_COOKIES_LIVE_MODE', $values['AXEPTIO_COOKIES_LIVE_MODE']);
        Configuration::updateValue('AXEPTIO_COOKIES_ID_KEY', $values['AXEPTIO_COOKIES_ID_KEY']);
        Configuration::updateValue('AXEPTIO_COOKIES_VERSION', $values['AXEPTIO_COOKIES_VERSION']);

        $this->_clearCache($this->templateFile);
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
        $helper->submit_action = 'submitAxeptio_cookiesModule';

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
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $languages = Language::getLanguages(false);
        $fields = array();
        $live = 'AXEPTIO_COOKIES_LIVE_MODE';
        $key = 'AXEPTIO_COOKIES_ID_KEY';
        $ver = 'AXEPTIO_COOKIES_VERSION';

        foreach ($languages as $lang) {
            $id = (int)$lang['id_lang'];
            $fields[$live][$id] = Tools::getValue($live.'_'.$id, Configuration::get($live, $id));
            $fields[$key][$id] = Tools::getValue($key.'_'.$id, Configuration::get($key, $id));
            $fields[$ver][$id] = Tools::getValue($ver.'_'.$id, Configuration::get($ver, $id));
        }
        return $fields;
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch_with_lang',
                        'label' => $this->l('Live mode'),
                        'name' => 'AXEPTIO_COOKIES_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'lang' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter a valid Axeptio cookies data id'),
                        'name' => 'AXEPTIO_COOKIES_ID_KEY',
                        'label' => $this->l('ID'),
                        'lang' => true,
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Enter a valid Axeptio version name'),
                        'name' => 'AXEPTIO_COOKIES_VERSION',
                        'label' => $this->l('VERSION ID NAME'), //IDENTIFIANT DE LA VERSION
                        'lang' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function hookDisplayFooter()
    {
        // If live mode == 1 for current language, display cached version if possible
        if (Configuration::get('AXEPTIO_COOKIES_LIVE_MODE', $this->context->language->id) == 1) :
            if (!$this->isCached($this->templateFile, $this->getCacheId('axeptio_cookies'))) {
                $this->smarty->assign($this->getWidgetVariables());
            }

            if ($this->ps_version === true) {
                return $this->fetch($this->templateFile, $this->getCacheId('axeptio_cookies'));
            } else {
                return $this->display(__FILE__, $this->templateFile, $this->getCacheId('axeptio_cookies'));
            }
        endif;
    }

    public function getWidgetVariables()
    {
        return array(
            'AXEPTIO_COOKIES_ID_KEY' => Configuration::get('AXEPTIO_COOKIES_ID_KEY', $this->context->language->id),
            'AXEPTIO_COOKIES_VERSION' => Configuration::get('AXEPTIO_COOKIES_VERSION', $this->context->language->id)
        );
    }
}
