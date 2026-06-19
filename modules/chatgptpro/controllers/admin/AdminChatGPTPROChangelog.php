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

class AdminChatGPTPROChangelogController extends ModuleAdminController

{

    public $available_fields;

    public $name = 'chatgptpro';

    public function __construct()
    {

        $this->bootstrap = true;

        $this->meta_title = 'OpenAI Integration PRO Module Changelog';

        $this->module_name = 'chatgptpro';

        $this->mod = 'WEBLIR_' . strtoupper($this->module_name);

        $this->className = 'AdminChatGPTPROChangelog';

        parent::__construct();

        if (!$this->module->active) {

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));

        }

    }

    public function renderView()
    {

        $return = "";

        $return .= $this->displayUsageSummary();

        return $return;

    }

    public function psversion()
    {

        $version = _PS_VERSION_;

        $ver = explode('.', $version);

        return $ver[1];

    }

    private function displayUsageSummary()
    {

        $shop = Tools::getHttpHost(true) . __PS_BASE_URI__;
        $module = Module::getInstanceByName('chatgptpro');
        $version = $module->version;

        $this->context->smarty->assign([

            'path' => _PS_BASE_URL_SSL_.  __PS_BASE_URI__ . 'modules/chatgptpro/',

            'shop' => $shop,

            'current_lang' => $this->context->language->id,

            'current_module_version' => $version,

            'lang_list' => Language::getLanguages(true),

        ]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'chatgptpro/views/templates/hook/changelog.tpl');

    }

    public function postProcess()
    {
        // nothing to do here
    }


    public function initContent()
    {
        $this->content = $this->renderView();
        parent::initContent();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();
    }

}

