<?php

/**

* 2007-2023 weblir

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

*  @author    weblir <contact@weblir.com>

*  @copyright 2007-2023 weblir

*  @license   weblir.com

*/

class AdminChatGPTPROUsageController extends ModuleAdminController

{

    public $available_fields;

    public $name = 'chatgptpro';

    public function __construct()
    {

        $this->bootstrap = true;

        $this->meta_title = 'OpenAI Account Usage';

        $this->module_name = 'chatgptpro';

        $this->mod = 'WEBLIR_' . strtoupper($this->module_name);

        parent::__construct();

        if (!$this->module->active) {

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));

        }

    }

    public function renderView()
    {

        $return = $this->generateLogList();

        if (Tools::getIsset('retrieveUsage')) {

            $date = Tools::getValue("usage_date");

            if (!Validate::isDate($date)) {

                $return .= Tools::displayError('Selected date is not valid!');

                $return .= $this->renderUsageDatePicker();

            } else {

                $return .= $this->renderUsageDatePicker();

                $return .= $this->displayUsageSummary($date);

            }

        } else {

            $return .= $this->renderUsageDatePicker();

        }

        

        return $return;

    }

    public function psversion()
    {

        $version = _PS_VERSION_;

        $ver = explode('.', $version);

        return $ver[1];

    }

    private function displayUsageSummary($date)
    {

        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;

        $usage_data = $this->getChatUsage($date);

        $this->context->smarty->assign([

            'path' => _PS_BASE_URL_SSL_.  __PS_BASE_URI__ . 'modules/chatgptpro/',

            'shop' => $shop,

            'usage_data' => $usage_data,

            'current_lang' => $this->context->language->id,

            'lang_list' => Language::getLanguages(true),

        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/usage.tpl');

    }

    public function postProcess()
    {
        if (Tools::getIsset('clear_log')) {
            $data = Db::getInstance()->Execute(
                'DELETE FROM ' . _DB_PREFIX_ . $this->module_name . '_log'
            );
            $this->confirmations[] = $this->l('Log list successfully deleted.');
        }
    }


    public function initContent()
    {

        $this->content = $this->renderView();

        parent::initContent();

    }

    public function setMedia($isNewTheme = false)
    {

        parent::setMedia();

        $this->addjQueryPlugin('tagify', null, false);

        $this->addjQueryPlugin('validate', null, false);

    }

    private function generateLogList()
    {
        if (Tools::getIsset('page')) {
            $page = (int)Tools::getValue('page');
        } else {
            $page = 1;
        }

        if (Tools::getIsset($this->module_name . '_log_pagination')) {
            $selected_pagination = (int)Tools::getValue($this->module_name . '_log_pagination');
        } else if (Tools::getIsset('selected_pagination')) {
            $selected_pagination = Tools::getValue('selected_pagination');
        } else {
            $selected_pagination = 50;
        }

        $content = $this->getLogHistory($page, $selected_pagination);

        foreach ($content as $key => $line) {
            $content[$key]['id_entity'] = $line['id_product'];
            if ($line['type'] == "singleCategory") {
                $content[$key]['id_entity'] = $line['id_category'];
            } elseif ($line['type'] == "singleCMS") {
                $content[$key]['id_entity'] = $line['id_cms'];
            } elseif ($line['type'] == "singleBrand") {
                $content[$key]['id_entity'] = $line['id_manufacturer'];
            }
        }

        $fields_list = array(
            'id_log' => array(
                'title' => 'ID',
                'align' => 'center',
                'search' => false,
                'class' => 'fixed-width-xs',
                'remove_onclick' => true
            ),
            'prompt' => array(
                'title' => $this->l('Prompt'),
                'search' => false,
                'remove_onclick' => true
            ),
            'reply' => array(
                'title' => $this->l('Reply'),
                'search' => false,
                'remove_onclick' => true
            ),
            'id_entity' => array(
                'title' => $this->l('Entity ID'),
                'search' => false,
                'remove_onclick' => true
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'search' => false,
                'remove_onclick' => true
            ),
            'date_updated' => array(
                'title' => $this->l('Timestamp'),
                'search' => false,
                'remove_onclick' => true
            )
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        //$helper->actions = array('delete');
        $helper->module = $this;
        $helper->toolbar_btn = array(
          'delete' => array(
              'desc' => $this->l('Clear list'),
              'href' => AdminController::$currentIndex.'?controller='.$this->className.'&clear_log'.
              '&token='.Tools::getAdminTokenLite('AdminChatGPTPROUsage'),
          )
        );
        $helper->listTotal = $this->countLogHistory();
        $helper->identifier = 'id_log';
        $helper->title = $this->l('Log History');
        $helper->table = $this->module_name.'_log';
        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPROUsage');
        $helper->currentIndex = AdminController::$currentIndex;

        return $helper->generateList($content, $fields_list);
    }

    public function getLogHistory($page = 1, $fields_list = 50)
    {
        if ($page == 1) {
            $offset = 0;
        } else {
            if ($page == 0) {
                $page = 1;
            }
            $offset = intval(($page-1))*$fields_list;
        }

        $sql = 'SELECT * FROM '._DB_PREFIX_.$this->module_name.'_log
            ORDER BY id_log DESC
            LIMIT '.$offset.', '.$fields_list;

        return Db::getInstance()->ExecuteS($sql);
    }

    public function countLogHistory()
    {
        $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.$this->module_name.'_log';

        return Db::getInstance()->getValue($sql);
    }

    public function renderUsageDatePicker()
    {

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $inputs[] = array(

                'type' => 'date',

                'label' => $this->l('Usage date'),

                'name' => 'usage_date',

                'desc' => $this->l('Select a date to display reported usage.'),

                'required' => false,

                'lang' => false,

        );

        $fields_form = [

            'form' => [

                'legend' => [

                    'title' => $this->l('Select usage date to display OpenAI Account Info'),

                    'icon' => 'icon-calendar',

                ],

                'input' => $inputs,

                'submit' => [

                    'title' => $this->l('Retrieve usage'),

                ]

            ],

        ];

        if (count($this->errors) > 0) {

            $keep_data = 1;

        } else {

            $keep_data = 1;

        }

        $helper = new HelperForm();

        $helper->show_toolbar = false;

        $helper->default_form_language = $lang->id;

        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get(

            'PS_BO_ALLOW_EMPLOYEE_FORM_LANG'

        ) : 0;

        $this->fields_form = [];

        $helper->identifier = $this->identifier;

        $helper->submit_action = 'retrieveUsage';

        $helper->currentIndex = self::$currentIndex;

        $helper->token = Tools::getAdminTokenLite('AdminChatGPTPROUsage');

        $helper->tpl_vars = [

            'fields_value' => $this->getConfigFieldsValues($keep_data),

            'languages' => $this->context->controller->getLanguages(),

            'id_language' => $this->context->language->id,

        ];

        return $helper->generateForm([$fields_form]);

    }

    public function getConfigFieldsValues()
    {

        $arr = [

            'usage_date' => (Tools::getIsset('usage_date') ? Tools::getValue('usage_date') : date("Y-m-d", time())),

        ];

        return $arr;

    }

    protected function getChatUsage($date)
    {

        $api_key = Configuration::get($this->mod . '_API_KEY');

        $date = $date;

        $url = 'https://api.openai.com/v1/usage?date=' . $date;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(

            'Content-Type: application/json',

            'Authorization: Bearer ' . $api_key,

        ));

        $response = curl_exec($ch);

        if (Configuration::get($this->mod . '_DEBUG_MODE') == 1) {

            dump($response);

        }

        if (curl_errno($ch)) {

            if (Configuration::get($this->mod . '_DEBUG_MODE') == 1) {

                dump('Error: ' . curl_error($ch));

            }

            return false;

        } else {

            $data = json_decode($response, true);

            curl_close($ch);

            return $data;

        }

    }

}

