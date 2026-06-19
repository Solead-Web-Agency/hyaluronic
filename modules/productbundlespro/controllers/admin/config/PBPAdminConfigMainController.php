<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Musaffar Patel
 * @copyright 2016-2019 Musaffar Patel
 * @license   LICENSE.txt
 */

class PBPAdminConfigMainController extends PBPControllerCore
{
    protected $sibling;

    public function __construct(&$sibling = null)
    {
        parent::__construct($sibling);
        if ($sibling !== null) {
            $this->sibling = &$sibling;
        }
    }

    public function setMedia()
    {
        if (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == 'productbundlespro') {
            Context::getContext()->controller->addCSS($this->sibling->_path . 'views/css/lib/tools.css');
            Context::getContext()->controller->addCSS($this->getAdminWebPath() . '/themes/new-theme/public/theme.css');
            Context::getContext()->controller->addCSS($this->getAdminWebPath() . '/themes/new-theme/public/theme.css');
            Context::getContext()->controller->addCSS($this->sibling->_path . 'views/css/admin/producttab.css');

            Context::getContext()->controller->addJquery();
            Context::getContext()->controller->addJS(_PS_BO_ALL_THEMES_DIR_ . 'default/js/tree.js');
            Context::getContext()->controller->removeJS(__PS_BASE_URI__ . 'js/jquery/plugins/fancybox/jquery.fancybox.js');
            Context::getContext()->controller->addJS($this->getAdminWebPath() . '/themes/new-theme/public/bundle.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/Tools.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/Breadcrumb.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/lib/pbpproductsearchwidget.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/config/PBPAdminConfigGeneralController.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/config/PBPAdminConfigTabEditController.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/config/PBPAdminConfigTabsController.js');
            Context::getContext()->controller->addJS($this->sibling->_path . 'views/js/admin/config/PBPAdminConfigMassAssignController.js');
        }
    }

    public function render()
    {
        Context::getContext()->smarty->assign(array(
            'module_config_url' => $this->module_config_url,
            'module_ajax_url_pbp' => $this->module_ajax_url
        ));
        return $this->sibling->display($this->sibling->module_file, 'views/templates/admin/config/main.tpl');
    }

    public function route()
    {
        return $this->render();
    }
}
