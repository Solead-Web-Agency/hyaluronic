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

class AdminChatGPTPROSettingsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'AdminChatGPTPROSettings';
        $this->meta_title = 'OpenAI Module Settings';
        $this->table_name = 'chatgptpro';
        $this->list_no_link = true;
        parent::__construct();
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }
    }

    public function renderView()
    {
        //  $this->postProcess();
        return '';
    }

    public function postProcess()
    {
    }

    public function initContent()
    {
        //Tools::redirect('index.php?controller=authentication?back=my-account');
        Tools::redirect($this->context->link->getAdminLink('AdminModules', false) .
            '&configure=' . $this->table_name . '&module_name=' . $this->table_name . '&token=' . Tools::getAdminTokenLite('AdminModules'));

        $this->content .= $this->renderView();
        
        parent::initContent();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia();
        $this->context->controller->addJS(_MODULE_DIR_ . $this->table_name . '/views/js/admin.js');
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->table_name . '/views/css/admin.css', 'all');
    }
}
